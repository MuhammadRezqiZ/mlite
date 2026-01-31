<?php

namespace Plugins\AsetKami;

use Systems\SiteModule;
use FPDF;

class Site extends SiteModule
{
    public function init()
    {
        // Tidak perlu authentication untuk akses publik
    }
    
    public function routes()
    {
        $this->route('asetkami/cetakriwayat', 'getCetakRiwayat');
        $this->route('asetkami/cetakkir', 'getCetakKir');
        $this->route('asetkami/caribarang', 'getCariBarang');
        $this->route('asetkami/decode', 'postDecode');
        $this->route('asetkami/cetakbarcode', 'getCetakBarcode');
        $this->route('asetkami/cetakqrcode', 'getCetakBarcode');
        $this->route('asetkami/cetakpdf', 'getCetakpdf');
        $this->route('asetkami/test', 'getTest');
    }
    
    public function getTest()
    {
        echo 'Test routing berhasil!';
        exit;
    }

    public function getCetakRiwayat()
    {
        try {
            $kode_barang = $_GET['kode_barang'] ?? '';
            
            if (empty($kode_barang)) {
                echo 'Kode barang tidak valid';
                exit;
            }
            
            // Ambil data barang dengan join ke tabel kategori dan jenis
            $barang = $this->db('aset_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
                ->select([
                    'aset_barang.*',
                    'aset_kategori.nm_kategori_barang',
                    'aset_jenis.nm_jns_barang'
                ])
                ->where('aset_barang.kode_barang', $kode_barang)
                ->oneArray();
                
            if (!$barang) {
                echo 'Data barang tidak ditemukan';
                exit;
            }
            
            // Ambil riwayat distribusi
            $distribusi = $this->db('aset_distribusi')
                ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit')
                ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_distribusi.kode_bidang')
                ->select([
                    'aset_distribusi.*',
                    'aset_unit.nama_unit',
                    'aset_bidang.nama_bidang'
                ])
                ->where('aset_distribusi.kode_barang', $kode_barang)
                ->asc('tanggal_distribusi')
                ->toArray();
                
            // Ambil kode distribusi terbaru untuk barang ini
            $kode_distribusi_terbaru = $this->db('aset_distribusi')
                ->select('kd_distribusi')
                ->where('kode_barang', $kode_barang)
                ->desc('tanggal_distribusi')
                ->limit(1)
                ->oneArray();
                
            $kd_distribusi = $kode_distribusi_terbaru ? $kode_distribusi_terbaru['kd_distribusi'] : '-';
            
            // Mapping unit dan bidang untuk riwayat
            $unit_map = [];
            $bidang_map = [];
            
            // Ambil semua unit dan bidang untuk mapping
            $units = $this->db('aset_unit')->toArray();
            foreach ($units as $unit) {
                $unit_map[$unit['kode_unit']] = $unit['nama_unit'];
            }
            
            $bidangs = $this->db('aset_bidang')->toArray();
            foreach ($bidangs as $bidang) {
                $bidang_map[$bidang['kode_bidang']] = $bidang['nama_bidang'];
            }
            
            // Buat array riwayat yang lebih lengkap
            $riwayat = [];
            
            // Tambahkan entry awal (barang masuk ke gudang)
            $riwayat[] = [
                'tanggal' => $barang['tanggal_input'] ?? date('Y-m-d H:i:s'),
                'kd_distribusi' => '-',
                'dari' => $barang['asal_barang'] ?? 'Supplier',
                'ke' => 'Gudang',
                'keterangan' => 'Barang masuk ke gudang',
                'user' => $barang['petugas'] ?? 'System'
            ];
            
            // Tambahkan riwayat distribusi
            $lokasi_sebelumnya = 'Gudang';
            foreach ($distribusi as $dist) {
                $lokasi_tujuan = ($bidang_map[$dist['kode_bidang']] ?? 'Unknown') . ' - ' . ($unit_map[$dist['kode_unit']] ?? 'Unknown');
                
                $riwayat[] = [
                    'tanggal' => $dist['tanggal_distribusi'],
                    'kd_distribusi' => $dist['kd_distribusi'] ?? '-',
                    'dari' => $lokasi_sebelumnya,
                    'ke' => $lokasi_tujuan,
                    'keterangan' => $dist['keterangan'] ?? 'Distribusi barang',
                    'user' => $dist['petugas']
                ];
                
                $lokasi_sebelumnya = $lokasi_tujuan;
            }
            
            // Urutkan riwayat berdasarkan tanggal
            usort($riwayat, function($a, $b) {
                return strtotime($a['tanggal']) - strtotime($b['tanggal']);
            });
            
            // Gunakan FPDF untuk konversi ke PDF
            $pdf = new FPDF();
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 16);
            
            // Header
            $pdf->Cell(0, 10, $this->settings->get('settings.nama_instansi'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 6, $this->settings->get('settings.alamat'), 0, 1, 'C');
            $pdf->Cell(0, 6, 'Telp: ' . $this->settings->get('settings.nomor_telepon'), 0, 1, 'C');
            $pdf->Ln(5);
            $pdf->Cell(0, 0, '', 'T', 1);
            $pdf->Ln(5);
            
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'RIWAYAT DISTRIBUSI BARANG', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Informasi Barang
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'INFORMASI BARANG', 0, 1);
            $pdf->SetFont('Arial', '', 10);
           // $pdf->Cell(40, 6, 'Kode Barang', 0, 0);
           // $pdf->Cell(5, 6, ':', 0, 0);
           // $pdf->Cell(0, 6, $barang['kode_barang'], 0, 1);
            $pdf->Cell(40, 6, 'Kode Distribusi', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, $kd_distribusi, 0, 1);
            $pdf->Cell(40, 6, 'Nama Barang', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, $barang['nm_jns_barang'], 0, 1);
            $pdf->Cell(40, 6, 'Kategori', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, $barang['nm_kategori_barang'], 0, 1);
            $pdf->Cell(40, 6, 'Spesifikasi', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, ($barang['spesifikasi'] ?? '-'), 0, 1);
            $pdf->Ln(10);
            
            // Riwayat Distribusi
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'RIWAYAT DISTRIBUSI', 0, 1);
            
            // Header tabel
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(10, 8, 'No', 1, 0, 'C');
            $pdf->Cell(25, 8, 'Tanggal', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Dari', 1, 0, 'C');
            $pdf->Cell(40, 8, 'Ke', 1, 0, 'C');
            $pdf->Cell(50, 8, 'Keterangan', 1, 0, 'C');
            $pdf->Cell(25, 8, 'User', 1, 1, 'C');
            
            // Data riwayat
            $pdf->SetFont('Arial', '', 8);
            $no = 1;
            foreach ($riwayat as $row) {
                $pdf->Cell(10, 6, $no, 1, 0, 'C');
                $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['tanggal'])), 1, 0, 'C');
                $pdf->Cell(40, 6, substr($row['dari'], 0, 25), 1, 0);
                $pdf->Cell(40, 6, substr($row['ke'], 0, 25), 1, 0);
                $pdf->Cell(50, 6, substr($row['keterangan'], 0, 32), 1, 0);
                $pdf->Cell(25, 6, substr($row['user'], 0, 15), 1, 1, 'C');
                $no++;
            }
            

            
            $pdf->Ln(10);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 6, 'Dicetak pada: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
            
            // Set header untuk download PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Riwayat_' . $barang['kode_barang'] . '_' . date('Y-m-d') . '.pdf"');
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');
            
            $pdf->Output('I', 'Riwayat_' . $barang['kode_barang'] . '_' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            // Fallback jika FPDF tidak tersedia
            header('Content-Type: text/html; charset=utf-8');
            echo '<h3>Error: ' . $e->getMessage() . '</h3>';
            echo '<p>Silakan hubungi administrator sistem.</p>';
        }
        exit;
    }
    
    public function postDecode()
    {
        header('Content-Type: application/json');
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $kd_distribusi = $input['kd_distribusi'] ?? '';
            
            if (empty($kd_distribusi)) {
                echo json_encode(['success' => false, 'message' => 'Kode distribusi tidak valid']);
                exit;
            }
            

            
            // Cari barang berdasarkan kode distribusi atau kode barang
            $distribusi = $this->db('aset_distribusi')
                ->join('aset_barang', 'aset_barang.kode_barang=aset_distribusi.kode_barang')
                ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit')
                ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_distribusi.kode_bidang')
                ->select([
                    'aset_barang.kode_barang',
                    'aset_barang.merk',
                    'aset_barang.model_seri',
                    'aset_jenis.nm_jns_barang',
                    'aset_kategori.nm_kategori_barang',
                    'aset_distribusi.tanggal_distribusi',
                    'aset_distribusi.kd_distribusi',
                    'aset_unit.nama_unit',
                    'aset_bidang.nama_bidang'
                ])
                ->where('aset_distribusi.kd_distribusi', $kd_distribusi)
                ->orWhere('aset_barang.kode_barang', $kd_distribusi)
                ->oneArray();

                
            if ($distribusi) {
                $distribusi['lokasi_saat_ini'] = $distribusi['nama_bidang'] . ' - ' . $distribusi['nama_unit'];
                $distribusi['tanggal_distribusi'] = date('d/m/Y', strtotime($distribusi['tanggal_distribusi']));
                
                $response = [
                    'success' => true,
                    'data' => $distribusi,
                    'redirect_url' => '/asetkami/cetakriwayat?kode_barang=' . $distribusi['kode_barang']
                ];

                echo json_encode($response);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kode distribusi tidak ditemukan']);
            }
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function getCetakKir()
    {
        try {
            $kode_bidang = $_GET['kode_bidang'] ?? '';
            $kode_unit = $_GET['kode_unit'] ?? '';
            
            if (empty($kode_bidang) || empty($kode_unit)) {
                echo 'Parameter tidak lengkap';
                exit;
            }
            
            // Ambil data bidang dan unit
            $bidang = $this->db('aset_bidang')->where('kode_bidang', $kode_bidang)->oneArray();
            $unit = $this->db('aset_unit')->where('kode_unit', $kode_unit)->oneArray();
            
            if (!$bidang || !$unit) {
                echo 'Data bidang atau unit tidak ditemukan';
                exit;
            }
            
            // Ambil data barang KIR
            $sql = "SELECT 
                ab.id,
                ab.kode_barang,
                aj.nm_jns_barang,
                ak.nm_kategori_barang,
                ab.merk,
                ab.model_seri,
                ab.sn,
                ab.kelengkapan,
                ab.harga,
                ab.tanggal_input,
                ad.kode_bidang,
                ad.kode_unit,
                ad.tanggal_distribusi,
                ad.kd_distribusi,
                ad.petugas,
                ad.keterangan
            FROM aset_distribusi ad
            JOIN aset_barang ab ON ab.kode_barang = ad.kode_barang
            JOIN aset_jenis aj ON aj.kd_jns_barang = ab.kd_jns_barang
            JOIN aset_kategori ak ON ak.kd_kategori = ab.kd_kategori
            WHERE ad.kode_bidang = ? AND ad.kode_unit = ?
            AND ad.tanggal_distribusi = (
                SELECT MAX(tanggal_distribusi) 
                FROM aset_distribusi ad2 
                WHERE ad2.kode_barang = ad.kode_barang
            )
            ORDER BY ad.tanggal_distribusi DESC";
            
            $stmt = $this->db()->pdo()->prepare($sql);
            $stmt->execute([$kode_bidang, $kode_unit]);
            $dataKir = $stmt->fetchAll();
            
            // Buat PDF
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            
            // Header
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, $this->settings->get('settings.nama_instansi'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 6, $this->settings->get('settings.alamat'), 0, 1, 'C');
            $pdf->Cell(0, 6, 'Telp: ' . $this->settings->get('settings.nomor_telepon'), 0, 1, 'C');
            $pdf->Ln(5);
            
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'KIR (KARTU INVENTARIS ASET)', 0, 1, 'C');
            $pdf->Ln(5);
            
            // Informasi Lokasi
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'INFORMASI LOKASI', 0, 1);
            $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
            $pdf->Ln(3);
            
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(30, 6, 'Bidang', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, $bidang['nama_bidang'], 0, 1);
            
            $pdf->Cell(30, 6, 'Unit/Ruangan', 0, 0);
            $pdf->Cell(5, 6, ':', 0, 0);
            $pdf->Cell(0, 6, $unit['nama_unit'], 0, 1);
            
            $pdf->Ln(5);
            
            // Data KIR
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, 'DATA BARANG', 0, 1);
            $pdf->Line(10, $pdf->GetY(), 287, $pdf->GetY());
            $pdf->Ln(3);
            
            if (empty($dataKir)) {
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(0, 10, 'Tidak ada data barang di lokasi ini', 0, 1, 'C');
            } else {
                // Header tabel
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(8, 8, 'No', 1, 0, 'C');
                $pdf->Cell(25, 8, 'Kode Barang', 1, 0, 'C');
                $pdf->Cell(45, 8, 'Nama Barang', 1, 0, 'C');
                $pdf->Cell(20, 8, 'Merk', 1, 0, 'C');
                $pdf->Cell(20, 8, 'SN', 1, 0, 'C');
                $pdf->Cell(25, 8, 'Tgl Distribusi', 1, 0, 'C');
                $pdf->Cell(40, 8, 'Kd Distribusi', 1, 0, 'C');
                $pdf->Cell(20, 8, 'Petugas', 1, 0, 'C');
                $pdf->Cell(35, 8, 'Keterangan', 1, 1, 'C');
                
                // Data tabel
                $pdf->SetFont('Arial', '', 7);
                $no = 1;
                $totalNilai = 0;
                
                foreach ($dataKir as $row) {
                    $pdf->Cell(8, 6, $no, 1, 0, 'C');
                    $pdf->Cell(25, 6, substr($row['kode_barang'], 0, 15), 1, 0);
                    $pdf->Cell(45, 6, substr($row['nm_jns_barang'], 0, 28), 1, 0);
                    $pdf->Cell(20, 6, substr($row['merk'] ?? '-', 0, 12), 1, 0);
                    $pdf->Cell(20, 6, substr($row['sn'] ?? '-', 0, 12), 1, 0);
                    $pdf->Cell(25, 6, date('d/m/Y', strtotime($row['tanggal_distribusi'])), 1, 0, 'C');
                    $pdf->Cell(40, 6, $row['kd_distribusi'] ?? '-', 1, 0, 'C');
                    $pdf->Cell(20, 6, substr($row['petugas'] ?? '-', 0, 12), 1, 0);
                    $pdf->Cell(35, 6, substr($row['keterangan'] ?? '-', 0, 22), 1, 1);
                    
                    $totalNilai += floatval($row['harga']);
                    $no++;
                }
                
                // Summary
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(100, 6, 'Total Barang: ' . count($dataKir) . ' item', 0, 0);
                $pdf->Cell(0, 6, 'Total Nilai: Rp ' . number_format($totalNilai, 0, ',', '.'), 0, 1, 'R');
            }
            
            $pdf->Ln(10);
            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(0, 6, 'Dicetak pada: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
            
            // Set header untuk PDF preview
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="KIR_' . $bidang['nama_bidang'] . '_' . $unit['nama_unit'] . '_' . date('Y-m-d') . '.pdf"');
            
            $pdf->Output('I', 'KIR_' . $bidang['nama_bidang'] . '_' . $unit['nama_unit'] . '_' . date('Y-m-d') . '.pdf');
            
        } catch (\Exception $e) {
            header('Content-Type: text/html; charset=utf-8');
            echo '<h3>Error: ' . $e->getMessage() . '</h3>';
            echo '<p>Silakan hubungi administrator sistem.</p>';
        }
        exit;
    }
    
    public function getCariBarang()
    {
        header('Content-Type: application/json');
        
        $kd_distribusi = $_GET['kd_distribusi'] ?? '';
        
        if (empty($kd_distribusi)) {
            echo json_encode(['success' => false, 'message' => 'Kode distribusi tidak valid']);
            exit;
        }
        
        try {
            // Cari barang berdasarkan kode distribusi
            $distribusi = $this->db('aset_distribusi')
                ->join('aset_barang', 'aset_barang.kode_barang=aset_distribusi.kode_barang')
                ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit')
                ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_distribusi.kode_bidang')
                ->select([
                    'aset_barang.kode_barang',
                    'aset_barang.merk',
                    'aset_barang.model_seri',
                    'aset_jenis.nm_jns_barang',
                    'aset_kategori.nm_kategori_barang',
                    'aset_distribusi.tanggal_distribusi',
                    'aset_distribusi.kd_distribusi',
                    'aset_unit.nama_unit',
                    'aset_bidang.nama_bidang'
                ])
                ->where('aset_distribusi.kd_distribusi', $kd_distribusi)
                ->oneArray();
                
            if ($distribusi) {
                $distribusi['lokasi_saat_ini'] = $distribusi['nama_bidang'] . ' - ' . $distribusi['nama_unit'];
                $distribusi['tanggal_distribusi'] = date('d/m/Y', strtotime($distribusi['tanggal_distribusi']));
                
                echo json_encode([
                    'success' => true,
                    'data' => $distribusi
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Kode distribusi tidak ditemukan']);
            }
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function getCetakBarcode()
    {
        $kode_barang = $_GET['kode_barang'] ?? '';
        
        if (empty($kode_barang)) {
            echo 'Kode barang tidak valid';
            exit;
        }
        
        // Ambil data barang dan distribusi
        $distribusi = $this->db('aset_distribusi')
            ->join('aset_barang', 'aset_barang.kode_barang=aset_distribusi.kode_barang')
            ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
            ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
            ->select([
                'aset_barang.*',
                'aset_kategori.nm_kategori_barang',
                'aset_jenis.nm_jns_barang',
                'aset_distribusi.kd_distribusi'
            ])
            ->where('aset_barang.kode_barang', $kode_barang)
            ->oneArray();
            
        if (!$distribusi) {
            echo 'Data barang atau distribusi tidak ditemukan';
            exit;
        }
        
        // Ambil logo dari settings
        $logo = $this->settings->get('settings.logo');
        $logoPath = '';
        if (!empty($logo)) {
            $logoPath = WEBAPPS_PATH . '/uploads/settings/' . $logo;
            if (file_exists($logoPath)) {
                $logoData = base64_encode(file_get_contents($logoPath));
                $logoMimeType = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMimeType . ';base64,' . $logoData;
            } else {
                $logoBase64 = '';
            }
        } else {
            $logoBase64 = '';
        }
        
        // Generate QR code using local PHP library
        require_once __DIR__ . '/../../systems/lib/QRCode.php';
        
        $qr = new \Systems\Lib\QRCode();
        $qr->setTypeNumber(4); // Use higher type number for longer data
        $qr->addData($distribusi['kd_distribusi']); // Use kd_distribusi instead of kode_barang
        $qr->make();
        
        // Generate QR code as base64 image
        $qrImage = $qr->createImage(4, 2);
        
        ob_start();
        imagepng($qrImage);
        $qrImageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($qrImage);
        
        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrImageData);
        
        // Create HTML page with format sesuai gambar
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>QR Code Distribusi Barang</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px;
        }
        .container {
            width: 40%;
            max-width: 200px;
            margin: 0 auto;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #000;
            margin-bottom: 20px;
        }
        .header-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            font-size: 12px;
        }
        .logo-cell {
            width: 80px;
            height: 60px;
        }
        .logo-cell img {
            max-width: 80px;
            max-height: 60px;
            object-fit: contain;
        }
        .kode-cell {
            width: 200px;
        }
        .nama-cell {
            flex: 1;
        }
        .qr-cell {
            width: 100px;
            height: 60px;
        }
        .qr-cell img {
            width: 60px;
            height: 60px;
        }
        @media print {
            body { margin: 0; }
            .container { max-width: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-cell">';
                
            $html .= '<img src="' . url('/plugins/asetkami/img/hst.png') . '" alt="Logo" style="float: left; width: 70px; height: 100px; margin-left: 1px; margin-right: 1px;">';
        
        $html .= '</td>
                <td class="kode-cell">
                    ' . htmlspecialchars($distribusi['kd_distribusi']) . '
                    <br>' . htmlspecialchars($distribusi['kode_barang']) . '<br>
                    ' . htmlspecialchars($distribusi['nm_jns_barang']) . '
                </td>               
                <td class="qr-cell">
                    <img src="' . $qrBase64 . '" alt="QR Code">
                </td>
            </tr>
        </table>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>';
        
        // Set headers for HTML display
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        
        exit;
    }
    
    public function getCetakpdf()
    {
        // Ambil parameter filter dari URL
        $tanggalDari = isset($_GET['tanggal_dari']) ? $_GET['tanggal_dari'] : '';
        $tanggalKe = isset($_GET['tanggal_ke']) ? $_GET['tanggal_ke'] : '';
        $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
        $asalBarang = isset($_GET['asal_barang']) ? $_GET['asal_barang'] : '';
        $lokasi = isset($_GET['lokasi']) ? $_GET['lokasi'] : '';
        
        // Query untuk mengambil data aset dengan filter
        $sql = "SELECT 
            ab.id,
            ab.kode_barang,
            ab.kd_jns_barang,
            ab.kd_kategori,
            ab.asal_barang,
            ab.merk,
            ab.model_seri,
            ab.sn,
            ab.tanggal_input,
            ab.petugas as petugas_barang,
            ak.nm_kategori_barang, 
            aj.nm_jns_barang,
            ad.kd_distribusi,
            ad.tanggal_distribusi,
            ad.petugas as petugas_distribusi,
            ad.kode_unit,
            ad.kode_bidang,
            au.nama_unit,
            ab2.nama_bidang,
            mu1.fullname as fullname_petugas_barang,
            mu2.fullname as fullname_petugas_distribusi,
            CASE 
                WHEN ab.status = 1 THEN 'gudang'
                WHEN ab.status = 2 THEN 'terdistribusi'
                ELSE 'gudang'
            END as status
        FROM aset_barang ab
        JOIN aset_kategori ak ON ak.kd_kategori = ab.kd_kategori
        JOIN aset_jenis aj ON aj.kd_jns_barang = ab.kd_jns_barang
        LEFT JOIN mlite_users mu1 ON mu1.username = ab.petugas
        LEFT JOIN aset_distribusi ad ON ad.kode_barang = ab.kode_barang 
            AND ad.tanggal_distribusi = (
                SELECT MAX(tanggal_distribusi) 
                FROM aset_distribusi ad2 
                WHERE ad2.kode_barang = ab.kode_barang
            )
        LEFT JOIN mlite_users mu2 ON mu2.username = ad.petugas
        LEFT JOIN aset_unit au ON au.kode_unit = ad.kode_unit AND au.kode_bidang = ad.kode_bidang
        LEFT JOIN aset_bidang ab2 ON ab2.kode_bidang = ad.kode_bidang
        WHERE 1=1";
        
        $params = [];
        
        // Filter berdasarkan kategori
        if (!empty($kategori)) {
            $sql .= " AND ak.nm_kategori_barang = ?";
            $params[] = $kategori;
        }
        
        // Filter berdasarkan asal barang
        if (!empty($asalBarang)) {
            if ($asalBarang === 'Lainnya') {
                // Filter untuk menampilkan asal barang selain Pengadaan, Hibah, Bantuan, Sumbangan
                $sql .= " AND ab.asal_barang NOT IN ('Pengadaan', 'Hibah', 'Bantuan', 'Sumbangan')";
            } else {
                $sql .= " AND ab.asal_barang = ?";
                $params[] = $asalBarang;
            }
        }
        
        // Filter berdasarkan lokasi
        if (!empty($lokasi)) {
            if ($lokasi === 'Gudang') {
                // Barang di gudang adalah yang belum terdistribusi (status = 0)
                $sql .= " AND ab.status = 0";
            } else {
                // Barang yang sudah terdistribusi ke unit/bidang tertentu (status = 1)
                $sql .= " AND ab.status = 1 AND (ab2.nama_bidang LIKE ? OR au.nama_unit LIKE ?)";
                $params[] = '%' . $lokasi . '%';
                $params[] = '%' . $lokasi . '%';
            }
        }
        
        // Filter berdasarkan tanggal
        if (!empty($tanggalDari) && !empty($tanggalKe)) {
            $sql .= " AND DATE(ab.tanggal_input) BETWEEN ? AND ?";
            $params[] = $tanggalDari;
            $params[] = $tanggalKe;
        } elseif (!empty($tanggalDari)) {
            $sql .= " AND DATE(ab.tanggal_input) >= ?";
            $params[] = $tanggalDari;
        } elseif (!empty($tanggalKe)) {
            $sql .= " AND DATE(ab.tanggal_input) <= ?";
            $params[] = $tanggalKe;
        }
        
        $sql .= " ORDER BY ab.id DESC";
        
        $stmt = $this->db()->pdo()->prepare($sql);
        $stmt->execute($params);
        $aset = $stmt->fetchAll();
        
        // Format data sesuai konsep baru
        foreach ($aset as &$item) {
            // Semua barang menggunakan tanggal_input dari aset_barang (tanggal pengadaan)
            $item['tanggal_distribusi'] = date('d/m/Y', strtotime($item['tanggal_input']));
            
            if ($item['status'] == 'terdistribusi') {
                // Jika barang sudah terdistribusi, ambil data lokasi dari aset_distribusi
                if (!empty($item['nama_unit'])) {
                    $item['lokasi'] = $item['nama_bidang'] . ' - ' . $item['nama_unit'];
                } else {
                    $item['lokasi'] = $item['kode_unit'] ?? 'Unit tidak diketahui';
                }
                // Gunakan fullname jika tersedia, fallback ke username
                $item['petugas'] = $item['fullname_petugas_distribusi'] ?? $item['petugas_distribusi'];
            } else {
                // Jika barang belum terdistribusi, ambil data dari aset_barang
                $item['kd_distribusi'] = $item['kd_distribusi'] ?? '-';
                $item['lokasi'] = 'Gudang';
                // Gunakan fullname jika tersedia, fallback ke username
                $item['petugas'] = $item['fullname_petugas_barang'] ?? $item['petugas_barang'];
            }
        }
        
        // Generate HTML Preview
        $this->generateHTMLPreview($aset, $tanggalDari, $tanggalKe, $kategori, $asalBarang, $lokasi);
    }
    
    private function generateHTMLPreview($data, $tanggalDari = '', $tanggalKe = '', $kategori = '', $asalBarang = '', $lokasi = '')
    {
        // Filter info
        $filterInfo = '';
        if (!empty($kategori)) {
            $filterInfo .= 'Kategori: ' . htmlspecialchars($kategori) . ' | ';
        }
        if (!empty($asalBarang)) {
            if ($asalBarang === 'Lainnya') {
                $filterInfo .= 'Asal Barang: Lainnya (selain Pengadaan, Hibah, Bantuan, Sumbangan) | ';
            } else {
                $filterInfo .= 'Asal Barang: ' . htmlspecialchars($asalBarang) . ' | ';
            }
        }
        if (!empty($lokasi)) {
            $filterInfo .= 'Lokasi: ' . htmlspecialchars($lokasi) . ' | ';
        }
        if (!empty($tanggalDari) && !empty($tanggalKe)) {
            $filterInfo .= 'Tanggal: ' . date('d/m/Y', strtotime($tanggalDari)) . ' - ' . date('d/m/Y', strtotime($tanggalKe));
        } elseif (!empty($tanggalDari)) {
            $filterInfo .= 'Tanggal: >= ' . date('d/m/Y', strtotime($tanggalDari));
        } elseif (!empty($tanggalKe)) {
            $filterInfo .= 'Tanggal: <= ' . date('d/m/Y', strtotime($tanggalKe));
        }
        
        // Remove trailing ' | ' if exists
        $filterInfo = rtrim($filterInfo, ' | ');
        
        if (empty($filterInfo)) {
            $filterInfo = 'Semua Data';
        }
        
        // Generate table rows
        $tableRows = '';
        $no = 1;
        foreach ($data as $item) {
            $tableRows .= '<tr>';
            $tableRows .= '<td style="text-align: center;">' . $no . '</td>';
            $tableRows .= '<td style="text-align: center;">' . htmlspecialchars($item['kd_distribusi'] ?? '-') . '</td>';
            $tableRows .= '<td style="text-align: center;">' . htmlspecialchars($item['tanggal_distribusi']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['nm_kategori_barang']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['asal_barang']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['nm_jns_barang']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['merk']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['model_seri']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['sn']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['lokasi']) . '</td>';
            $tableRows .= '<td>' . htmlspecialchars($item['petugas']) . '</td>';
            $tableRows .= '</tr>';
            $no++;
        }
        
        // Create HTML page
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Laporan Data Aset</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; font-size: 18px; font-weight: bold; margin-bottom: 10px; }
        .filter-info { text-align: center; font-size: 12px; margin-bottom: 5px; }
        .print-date { text-align: center; font-size: 12px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #000; padding: 4px; }
        th { background-color: #f0f0f0; font-weight: bold; text-align: center; }
        .footer { text-align: left; font-size: 10px; margin-top: 20px; }
        .print-btn { margin: 20px 0; text-align: center; }
        .print-btn button { padding: 10px 20px; font-size: 14px; background-color:rgb(255, 0, 0); color: white; border: none; cursor: pointer; }
        @media print {
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">LAPORAN DATA ASET</div>
    <div class="filter-info">Filter: ' . $filterInfo . '</div>
    <div class="print-date">Tanggal Cetak: ' . date('d/m/Y H:i:s') . '</div>
    
    <table>
        <thead>
             <tr>
                 <th>No</th>
                 <th>Kode Dist.</th>
                 <th>Tgl Pengadaan</th>
                 <th>Kategori</th>
                 <th>Asal Barang</th>
                 <th>Jenis</th>
                 <th>Merk</th>
                 <th>Model/Seri</th>
                 <th>SN</th>
                 <th>Lokasi</th>
                 <th>Petugas</th>
             </tr>
         </thead>
        <tbody>
            ' . $tableRows . '
        </tbody>
    </table>
    
    <div class="footer">Total Data: ' . count($data) . ' item</div>
</body>
        <div class="print-btn">
                <button onclick="window.print()">Cetak PDF</button>
            </div>
</html>';
        
        // Set headers for HTML display
        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
    }

}
