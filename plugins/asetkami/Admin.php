<?php

namespace Plugins\Asetkami;

use Systems\AdminModule;
use FPDF;

class Admin extends AdminModule
{

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Tambah Barang' => 'tambah',
            'Gudang Aset' => 'gudang',
            'KIR' => 'kir',
            'Scanner' => 'scanner',
            'Pengaturan' => 'pengaturan',
            'Data Aset' => 'data'
        ];
    }

    public function getManage()
    {
        $sub_modules = [
            ['name' => 'Tambah Barang', 'url' => url([ADMIN, 'asetkami', 'tambah']), 'icon' => 'plus', 'desc' => 'Tambah data barang aset baru'],
            ['name' => 'Verifikasi Barang', 'url' => url([ADMIN, 'asetkami', 'verifikasi']), 'icon' => 'check', 'desc' => 'Verifikasi data barang aset'],
            // ['name' => 'Verifikasi Barang Tolak', 'url' => url([ADMIN, 'asetkami', 'verifikasitolak']), 'icon' => 'times', 'desc' => 'Verifikasi data barang aset yang ditolak'],
            ['name' => 'Gudang Aset', 'url' => url([ADMIN, 'asetkami', 'gudang']), 'icon' => 'archive', 'desc' => 'Kelola barang di gudang aset'],
            ['name' => 'Data Aset', 'url' => url([ADMIN, 'asetkami', 'data']), 'icon' => 'cubes', 'desc' => 'Lihat semua data aset'],
            ['name' => 'KIR', 'url' => url([ADMIN, 'asetkami', 'kir']), 'icon' => 'clipboard', 'desc' => 'Kartu Inventaris Aset'],
            ['name' => 'Scanner Barang', 'url' => url([ADMIN, 'asetkami', 'scanner']), 'icon' => 'qrcode', 'desc' => 'Scan QR Code untuk melihat riwayat barang'],
            ['name' => 'Verifikasi Pengembalian', 'url' => url([ADMIN, 'asetkami', 'kembali']), 'icon' => 'check', 'desc' => 'Verifikasi data pengembalian barang'],
            ['name' => 'Data Pengembalian Barang', 'url' => url([ADMIN, 'asetkami', 'kembali']), 'icon' => 'reply', 'desc' => 'Pengembalian data barang'],
            ['name' => 'Gudang Aset Bekas', 'url' => url([ADMIN, 'asetkami', 'gudangbekas']), 'icon' => 'archive', 'desc' => 'Kelola barang di gudang aset bekas'],
            ['name' => 'Master', 'url' => url([ADMIN, 'asetkami', 'pengaturan']), 'icon' => 'folder', 'desc' => 'Pengaturan jenis barang, kategori barang, dan unit per bidang']
        ];
        
        // Get statistics by category
        $stats = $this->getAsetStatistics();

        $pendingBarang = $this->db('aset_barang')
            ->where('aset_barang.status', '1')
            ->where('aset_barang.status_verifikasi', 'kirim')
            ->desc('aset_barang.id')
            ->toArray();
        
        $pendingBarangCount = count($pendingBarang);
        
        // $pendingBarangTolak = $this->db('aset_barang')
        //     ->where('aset_barang.status', '1')
        //     ->where('aset_barang.status_verifikasi', 'tolak')
        //     ->order('aset_barang.id', 'DESC')
        //     ->toArray();
        
        // $pendingBarangTolakCount = count($pendingBarangTolak);
        
        return $this->draw('manage.html', ['sub_modules' => $sub_modules, 'stats' => $stats, 'pendingBarangCount' => $pendingBarangCount]);
    }
    
    private function getAsetStatistics()
    {
        $categories = ['Alkes', 'Kantor', 'Rumah Tangga'];
        $stats = [];
        
        foreach ($categories as $category) {
            // Count items in warehouse (status = 1)
            $gudang = $this->db('aset_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->where('aset_kategori.nm_kategori_barang', $category)
                ->where('aset_barang.status', '1')
                ->count();
            
            // Count distributed items (status = 2)
            $terdistribusi = $this->db('aset_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->where('aset_kategori.nm_kategori_barang', $category)
                ->where('aset_barang.status', '2')
                ->count();
            
            // Count total items in category
            $total = $this->db('aset_barang')
                ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
                ->where('aset_kategori.nm_kategori_barang', $category)
                ->count();
            
            $stats[$category] = [
                'gudang' => $gudang,
                'terdistribusi' => $terdistribusi,
                'total' => $total
            ];
        }
        
        return $stats;
    }

    public function getPengaturan()
    {
        $sub_modules = [
            ['name' => 'Jenis Barang', 'url' => url([ADMIN, 'asetkami', 'jenis']), 'icon' => 'tags', 'desc' => 'Kelola jenis barang aset'],
            ['name' => 'Kategori Barang', 'url' => url([ADMIN, 'asetkami', 'kategori']), 'icon' => 'folder', 'desc' => 'Kelola kategori barang aset'],
            ['name' => 'Bidang', 'url' => url([ADMIN, 'asetkami', 'bidang']), 'icon' => 'building', 'desc' => 'Kelola data bidang'],
            ['name' => 'Unit per Bidang', 'url' => url([ADMIN, 'asetkami', 'unitbidang']), 'icon' => 'sitemap', 'desc' => 'Kelola data unit/ruangan per bidang'],
        ];
        return $this->draw('pengaturan.html', ['sub_modules' => $sub_modules]);
    }

    public function getTambah()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Tambah Barang Aset';
        $data['kategori'] = $this->db('aset_kategori')->toArray();
        $data['jenis'] = $this->db('aset_jenis')->toArray();
        return $this->draw('tambah.html', ['data' => $data]);
    }

    public function postTambah()
    {
        $foto = '';
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload_dir = BASE_DIR . '/uploads/aset/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $foto_name = time() . '_' . $_FILES['foto']['name'];
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_dir . $foto_name)) {
                $foto = $foto_name;
            }
        }

        // Handle asal barang
        $asal_barang = $_POST['asal_barang'];
        if ($asal_barang === 'dll' && !empty($_POST['asal_barang_lainnya'])) {
            $asal_barang = $_POST['asal_barang_lainnya'];
        }

        // Generate kode barang
        $kode_barang = 'AST' . date('YmdHis');
        
        $data = [
            'kode_barang' => $kode_barang,
            'kd_kategori' => $_POST['kd_kategori'],
            'kd_jns_barang' => $_POST['kd_jns_barang'],
            'merk' => $_POST['merk'],
            'model_seri' => $_POST['model_seri'],
            'sn' => $_POST['sn'],
            'tanggal_barang' => $_POST['tanggal_barang'],
            'kelengkapan' => $_POST['kelengkapan'],
            'stok' => 1, 
            'sumber_dana' => $_POST['sumber_dana'],
            'harga' => str_replace(['.', ','], ['', '.'], $_POST['harga']),
            'no_surat_permintaan' => $_POST['no_surat_permintaan'],
            'perusahaan_penyedia' => $_POST['perusahaan_penyedia'],
            'nama_pemilik_teknisi' => $_POST['nama_pemilik_teknisi'],
            'kontak' => $_POST['kontak'],
            'foto' => $foto,
            'asal_barang' => $asal_barang,
            'status' => '1', // Status '1' = barang di gudang
            'petugas' => $this->core->getUserInfo('username', null, true)
        ];

        if ($this->db('aset_barang')->save($data)) {
            $this->notify('success', 'Data barang berhasil ditambahkan');
        } else {
            $this->notify('failure', 'Gagal menambahkan data barang');
        }
        redirect(url([ADMIN, 'asetkami', 'tambah']));
    }

    public function getVerifikasi()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Verifikasi Barang Aset';
        
        // Ambil seluruh barang yang status=1 (di gudang) beserta info kategori & jenis
        $barang_gudang = $this->db('aset_barang')
            ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
            ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
            ->select([
                'aset_barang.*',
                'aset_kategori.nm_kategori_barang',
                'aset_jenis.nm_jns_barang'
            ])
            ->where('aset_barang.status', '1')
            ->where(function($q){
                $q->where('aset_barang.status_verifikasi', 'kirim');
                $q->orWhere('aset_barang.status_verifikasi', 'ditolak');
            })
            ->desc('aset_barang.id')
            ->toArray();

        $total_stok = 0;
        $bulanIndo = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        foreach ($barang_gudang as &$b) {
            $total_stok += (int)($b['stok'] ?? 0);
            if (!isset($b['status_verifikasi']) || $b['status_verifikasi'] === '' || $b['status_verifikasi'] === null) {
                $b['status_verifikasi'] = 'kirim';
            }
            if (!empty($b['tanggal_barang'])) {
                $tgl = date('Y-m-d', strtotime($b['tanggal_barang']));
                $pecah = explode('-', $tgl);
                $b['tanggal_barang'] = $pecah[2] . ' ' . $bulanIndo[$pecah[1]] . ' ' . $pecah[0];
            }
        }
        unset($b);

        $data['gudang'] = $barang_gudang;
        $data['total_stok'] = $total_stok;
        return $this->draw('verifikasi.html', ['data' => $data]);
    }
        
    // public function getVerifikasiTolak()
    // {
    //     $this->_addHeaderFiles();
    //     $data['title'] = 'Verifikasi Barang Aset Tolak';
        
    //     // Ambil seluruh barang yang status=1 (di gudang) beserta info kategori & jenis
    //     $barang_gudang = $this->db('aset_barang')
    //         ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
    //         ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
    //         ->select([
    //             'aset_barang.*',
    //             'aset_kategori.nm_kategori_barang',
    //             'aset_jenis.nm_jns_barang'
    //         ])
    //         ->where('aset_barang.status', '1')
    //         ->where('aset_barang.status_verifikasi', 'tolak')
    //         ->order('aset_barang.id', 'DESC')
    //         ->toArray();

    //     $total_stok = 0;
    //     foreach ($barang_gudang as &$b) {
    //         $total_stok += (int)($b['stok'] ?? 0);
    //         if (!isset($b['status_verifikasi']) || $b['status_verifikasi'] === '' || $b['status_verifikasi'] === '') {
    //             $b['status_verifikasi'] = 'tolak';
    //         }
    //     }
    //     unset($b);

    //     $data['gudang'] = $barang_gudang;
    //     $data['total_stok'] = $total_stok;
    //     return $this->draw('verifikasitolak.html', ['data' => $data]);
    // }

    // public function getVerifikasiDetail($id)
    // {
    //     $this->_addHeaderFiles();
    //     $data['title'] = 'Detail Verifikasi Barang Aset';
    //     $data['barang'] = $this->db('aset_barang')->where('id', $id)->oneArray();
    //     return $this->draw('verifikasi_detail.html', ['data' => $data]);
    // }

    public function getEditVerifikasi($id)
    {
      $this->_addHeaderFiles();
      
      $barang = $this->db('aset_barang')->toArray();
    
      $asetbarang = $this->db('aset_barang')
          ->where('aset_barang.id', $id)
          ->toArray();
    
      return $this->draw('verifikasi.html', [
          'asetbarang' => $asetbarang[0],
          'barang' => $barang,
      ]);
    }

    public function postUpdateVerifikasi()
    {
      // Terima id baik dari 'id' maupun 'barang_id'
      $id = isset($_POST['id']) ? $_POST['id'] : (isset($_POST['barang_id']) ? $_POST['barang_id'] : null);
      if (!$id) {
        redirect(url([ADMIN, 'asetkami', 'verifikasi']));
        return;
      }

      $cek = $this->db('aset_barang')->where('id', $id)->oneArray();
      if (!$cek) {
        redirect(url([ADMIN, 'asetkami', 'verifikasi']));
        return;
      }

      // Validasi status
      $allowed = ['kirim','verifikasi','ditolak'];
      $newStatus = isset($_POST['status_verifikasi']) ? $_POST['status_verifikasi'] : 'kirim';
      if (!in_array($newStatus, $allowed, true)) { $newStatus = 'kirim'; }

      $verifiedBy = $this->core->getUserInfo('username', null, true);

      // Hanya update field yang relevan agar tidak menimpa data lain
      $this->db('aset_barang')
        ->where('id', $id)
        ->update([
          'status_verifikasi' => $newStatus,
          'verified_by' => $verifiedBy,
        ]);

      redirect(url([ADMIN, 'asetkami', 'verifikasi'], ['edited' => 1]));
    }

    public function getGudang()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Gudang Aset';
        
        // Group by jenis barang
        $gudang_data = [];
        $total_stok = 0;
        $jenis_list = $this->db('aset_jenis')->toArray();
        
        foreach ($jenis_list as $jenis) {
            // Hitung stok berdasarkan jumlah data barang yang ada di gudang (status = 1)
            // Menghapus filter verified_by agar seluruh barang di gudang ditampilkan
            $barang_list = $this->db('aset_barang')
                ->where('kd_jns_barang', $jenis['kd_jns_barang'])
                ->where('status', '1')
                ->where('status_verifikasi', 'verifikasi')
                ->isNotNull('verified_by')
                ->toArray();
            
            $stok = 0;
            foreach ($barang_list as $barang) {
                $stok += $barang['stok'];
            }
            
            if ($stok > 0) {
                $gudang_data[] = [
                    'kd_jns_barang' => $jenis['kd_jns_barang'],
                    'nm_jns_barang' => $jenis['nm_jns_barang'],
                    'stok' => $stok,
                    'detail_url' => url([ADMIN, 'asetkami', 'detail', $jenis['kd_jns_barang']])
                ];
                $total_stok += $stok;
            }
        }
        
        $data['gudang'] = $gudang_data;
        $data['total_stok'] = $total_stok;
        return $this->draw('gudang.html', ['data' => $data]);
    }

    public function getDetail($kd_jns_barang)
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Detail Barang';
        $data['jenis'] = $this->db('aset_jenis')->where('kd_jns_barang', $kd_jns_barang)->oneArray();
        $data['barang'] = $this->db('aset_barang')
            ->join('aset_kategori', 'aset_kategori.kd_kategori=aset_barang.kd_kategori')
            ->select('aset_barang.*, aset_kategori.nm_kategori_barang')
            ->where('aset_barang.kd_jns_barang', $kd_jns_barang)
            ->toArray();
        
        // Format tanggal untuk tampilan
        $bulanIndo = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        // Ambil data distribusi terakhir untuk setiap barang
        $kode_barangs = array_column($data['barang'], 'kode_barang');
        $distribusi_map = [];
        
        if (!empty($kode_barangs)) {
            $placeholders = implode(',', array_fill(0, count($kode_barangs), '?'));
            $sql_dist = "SELECT kode_barang, MAX(tanggal_distribusi) as tgl FROM aset_distribusi WHERE kode_barang IN ($placeholders) GROUP BY kode_barang";
            
            $stmt = $this->db()->pdo()->prepare($sql_dist);
            $stmt->execute($kode_barangs);
            $rows = $stmt->fetchAll();
            
            foreach ($rows as $row) {
                $distribusi_map[$row['kode_barang']] = $row['tgl'];
            }
        }

        foreach ($data['barang'] as &$barang) {
            $barang['tanggal_input_formatted'] = date('d/m/Y', strtotime($barang['tanggal_input']));
            
            $tgl_to_show = $barang['tanggal_barang'];
            
            // Handle 0000-00-00 as empty
            if ($tgl_to_show == '0000-00-00') {
                $tgl_to_show = '';
            }
            
            // Jika kosong, cari di map distribusi
            if (empty($tgl_to_show) && isset($distribusi_map[$barang['kode_barang']])) {
                $tgl_to_show = $distribusi_map[$barang['kode_barang']];
            }
            
            // Simpan raw date untuk form edit (YYYY-MM-DD)
            $barang['tanggal_barang_raw'] = !empty($tgl_to_show) ? date('Y-m-d', strtotime($tgl_to_show)) : '';
            
            if (!empty($tgl_to_show)) {
                $tgl = date('Y-m-d', strtotime($tgl_to_show));
                $pecah = explode('-', $tgl);
                if (count($pecah) == 3 && isset($bulanIndo[$pecah[1]])) {
                    $barang['tanggal_barang'] = $pecah[2] . ' ' . $bulanIndo[$pecah[1]] . ' ' . $pecah[0];
                }
            }
        }
        
        return $this->draw('detail.html', ['data' => $data]);
    }

    public function postDistribusi()
    {
        if (isset($_POST['kd_distribusi_edit']) && !empty($_POST['kd_distribusi_edit'])) {
            // Ambil kode_barang dari tabel aset_barang berdasarkan barang_id
            $barang = $this->db('aset_barang')->where('id', $_POST['barang_id'])->oneArray();
            if (!$barang) {
                $this->notify('failure', 'Barang tidak ditemukan');
                $redirect_url = $_POST['redirect_url'] ?? url([ADMIN, 'asetkami', 'distribusi']);
                redirect($redirect_url);
                return;
            }
            
            // Update
            $update_data = [
                'kode_barang' => $barang['kode_barang'], // menggunakan kode_barang dari tabel aset_barang
                'kode_bidang' => $_POST['bidang'],
                'kode_unit' => $_POST['unit_ruangan'],
                'ruangan' => $_POST['kd_bangsal'],
                'petugas' => $this->core->getUserInfo('username', null, true),
                'keterangan' => $_POST['keterangan']
            ];
            if ($this->db('aset_distribusi')->where('kd_distribusi', $_POST['kd_distribusi_edit'])->update($update_data)) {
                $this->notify('success', 'Data distribusi berhasil diupdate');
            } else {
                $this->notify('failure', 'Gagal mengupdate data distribusi');
            }
        } else {
            // Ambil kode_barang dari tabel aset_barang berdasarkan barang_id
            $barang = $this->db('aset_barang')->where('id', $_POST['barang_id'])->oneArray();
            if (!$barang) {
                $this->notify('failure', 'Barang tidak ditemukan');
                $redirect_url = $_POST['redirect_url'] ?? url([ADMIN, 'asetkami', 'distribusi']);
                redirect($redirect_url);
                return;
            }
            
            // Cek apakah barang pernah didistribusi sebelumnya (ada riwayat distribusi)
            $distribusi_sebelumnya = $this->db('aset_distribusi')
                ->where('kode_barang', $barang['kode_barang'])
                ->where('keterangan', 'LIKE', 'Return ke gudang:%')
                ->desc('tanggal_distribusi')
                ->oneArray();
            
            if ($distribusi_sebelumnya && $barang['status'] == '1') {
                // Barang pernah didistribusi dan sekarang di gudang (status = 1)
                // Gunakan kode distribusi yang sama, hanya update bidang dan unit
                $kd_distribusi = $distribusi_sebelumnya['kd_distribusi'];
                
                // Insert record distribusi ulang dengan kode distribusi yang sama
                $insert_data = [
                    'kd_distribusi' => $kd_distribusi,
                    'kode_barang' => $barang['kode_barang'],
                    'kode_bidang' => $_POST['bidang'],
                    'kode_unit' => $_POST['unit_ruangan'],
                    'tanggal_distribusi' => date('Y-m-d H:i:s'),
                    'tanggal_pengadaan' => $_POST['tanggal_barang'] ?? null,
                    'petugas' => $_POST['petugas'] ?: $this->core->getUserInfo('username', null, true),
                    'keterangan' => $_POST['keterangan'] ?: 'Distribusi ulang'
                ];
                
                if ($this->db('aset_distribusi')->save($insert_data)) {
                    // Update status barang menjadi '2' (didistribusi) dan kurangi stok
                    $jumlah_distribusi = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
                    $stok_baru = $barang['stok'] - $jumlah_distribusi;
                    $update_data = ['stok' => $stok_baru];
                    
                    if ($stok_baru == 0) {
                        $update_data['status'] = '2';
                    }
                    
                    $this->db('aset_barang')->where('id', $_POST['barang_id'])->update($update_data);
                    $this->notify('success', 'Barang berhasil didistribusi ulang dengan kode distribusi: ' . $kd_distribusi);
                } else {
                     $this->notify('failure', 'Gagal mendistribusi ulang barang');
                 }
             } else {
                 // Generate kode distribusi dengan urutan yang benar
                  $kd_distribusi = $this->generateKodeDistribusi($_POST);
                  
                  // Validasi stok - pastikan stok mencukupi
                  $jumlah_distribusi = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 1;
                  if ($barang['stok'] < $jumlah_distribusi) {
                      $this->notify('failure', 'Stok barang tidak mencukupi. Stok tersedia: ' . $barang['stok']);
                      $redirect_url = $_POST['redirect_url'] ?? url([ADMIN, 'asetkami', 'distribusi']);
                      redirect($redirect_url);
                      return;
                  }
                   
                   // Ambil kode_bidang dan kode_unit dari form
                   $kode_bidang = $_POST['bidang'];
                   $kode_unit = $_POST['unit_ruangan'];
                   
                   // Insert data distribusi
                   $insert_data = [
                       'kd_distribusi' => $kd_distribusi,
                       'kode_barang' => $barang['kode_barang'], // menggunakan kode_barang dari tabel aset_barang
                       'kode_bidang' => $kode_bidang,
                       'kode_unit' => $kode_unit,
                       'tanggal_distribusi' => date('Y-m-d H:i:s'),
                       'tanggal_pengadaan' => $_POST['tanggal_barang'] ?? null,
                       'petugas' => $_POST['petugas'] ?: $this->core->getUserInfo('username', null, true),
                       'keterangan' => $_POST['keterangan']
                   ];
                   
                   if ($this->db('aset_distribusi')->save($insert_data)) {
                       // Kurangi stok barang di gudang dan ubah status ke '2' (didistribusi)
                       $stok_baru = $barang['stok'] - $jumlah_distribusi;
                       $update_data = ['stok' => $stok_baru];
                       
                       // Jika stok menjadi 0, ubah status ke '2' (didistribusi)
                       if ($stok_baru == 0) {
                           $update_data['status'] = '2';
                       }
                       
                       $this->db('aset_barang')->where('id', $_POST['barang_id'])->update($update_data);
                       
                       $this->notify('success', 'Data distribusi berhasil ditambahkan dan stok barang telah dikurangi');
                   } else {
                       $this->notify('failure', 'Gagal menambahkan data distribusi');
                   }
               }
           }
        
        // Redirect ke halaman yang sesuai
        $redirect_url = $_POST['redirect_url'] ?? url([ADMIN, 'asetkami', 'distribusi']);
        redirect($redirect_url);
    }
    
    public function getCheckBarangReturn()
    {
        $barang_id = $_GET['barang_id'] ?? '';
        
        if (empty($barang_id)) {
            echo json_encode(['status' => 'error', 'message' => 'Barang ID tidak valid']);
            exit;
        }
        
        // Ambil data barang
        $barang = $this->db('aset_barang')->where('id', $barang_id)->oneArray();
        if (!$barang) {
            echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
            exit;
        }
        
        // Cek apakah barang pernah didistribusi dan di-return
        $distribusi_return = $this->db('aset_distribusi')
            ->where('kode_barang', $barang['kode_barang'])
            ->where('keterangan', 'LIKE', 'Return ke gudang:%')
            ->desc('tanggal_distribusi')
            ->oneArray();
        
        $is_return_barang = ($distribusi_return && $barang['status'] == '1');
        
        echo json_encode([
            'status' => 'success',
            'is_return_barang' => $is_return_barang,
            'kd_distribusi' => $is_return_barang ? $distribusi_return['kd_distribusi'] : null
        ]);
        exit;
    }

    private function generateKodeDistribusi($data)
    {
        $intraEktra = isset($data['intra_ektra']) ? trim($data['intra_ektra']) : '';
        $sumber     = isset($data['sumber']) ? trim($data['sumber']) : '';
        $bidang     = isset($data['bidang']) ? trim($data['bidang']) : '';
        $unitRuangan= isset($data['unit_ruangan']) ? trim($data['unit_ruangan']) : '';
        $tahun      = date('Y');

        // Normalisasi: pastikan semua segmen 2 digit (kecuali tahun dan urutan)
        $intraEktra = str_pad($intraEktra, 2, '0', STR_PAD_LEFT);
        $sumber     = str_pad($sumber, 2, '0', STR_PAD_LEFT);
        $bidang     = str_pad($bidang, 2, '0', STR_PAD_LEFT);
        $unitRuangan= str_pad($unitRuangan, 2, '0', STR_PAD_LEFT);

        // Ambil urutan terakhir untuk tahun berjalan (3 digit), reset setiap tahun
        // Gunakan SQL untuk mengambil MAX dari segmen terakhir setelah titik
        $pdo = $this->db()->pdo();
        $stmt = $pdo->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(kd_distribusi, '.', -1) AS UNSIGNED)) AS maxseq 
                               FROM aset_distribusi 
                               WHERE kd_distribusi LIKE :pattern");
        $likePattern = "%." . $tahun . ".%";
        $stmt->execute([':pattern' => $likePattern]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $maxSeq = isset($row['maxseq']) ? (int)$row['maxseq'] : 0;
        $urutan = str_pad($maxSeq + 1, 3, '0', STR_PAD_LEFT);

        // Format baru: INTRA.SUMBER.BIDANG.UNIT.TAHUN.URUTAN
        return $intraEktra . '.' . $sumber . '.' . $bidang . '.' . $unitRuangan . '.' . $tahun . '.' . $urutan;
    }

    public function getDeleteDistribusi($id)
    {
        // Kembalikan stok barang sebelum menghapus distribusi
        $distribusi = $this->db('aset_distribusi')->where('kd_distribusi', $id)->oneArray();
        if ($distribusi) {
            $barang = $this->db('aset_barang')->where('kode_barang', $distribusi['kode_barang'])->oneArray();
            if ($barang) {
                // Kembalikan 1 unit stok (karena setiap distribusi = 1 unit)
                $stok_baru = $barang['stok'] + 1;
                $update_data = ['stok' => $stok_baru];
                
                // Jika barang dikembalikan ke gudang, ubah status ke '1' (di gudang)
                if ($barang['status'] == '2') {
                    $update_data['status'] = '1';
                }
                
                $this->db('aset_barang')->where('kode_barang', $distribusi['kode_barang'])->update($update_data);
            }
            
            if ($this->db('aset_distribusi')->where('kd_distribusi', $id)->delete()) {
                $this->notify('success', 'Data distribusi berhasil dihapus');
            } else {
                $this->notify('failure', 'Gagal menghapus data distribusi');
            }
        }
        redirect(url([ADMIN, 'asetkami', 'distribusi']));
    }

    public function getDelete_distribusi($id)
    {
        return $this->getDeleteDistribusi($id);
    }

    public function getDistribusi()
    {
        $this->_addHeaderFiles();
        
        $data = [
            'distribusi' => $this->db('aset_distribusi')
                ->join('aset_barang', 'aset_barang.kode_barang = aset_distribusi.kode_barang')
                ->select([
                    'aset_distribusi.kd_distribusi',
                    'aset_distribusi.kode_bidang',
                    'aset_distribusi.kode_unit',
                    'aset_distribusi.tanggal_barang',
                    'aset_distribusi.tanggal_distribusi',
                    'aset_distribusi.petugas',
                    'aset_distribusi.keterangan',
                    'aset_barang.merk',
                    'aset_barang.model_seri',
                    'aset_barang.status'
                ])
                ->toArray(),
            'barang' => $this->db('aset_barang')
                ->where('stok', '>', 0)
                ->toArray(),
            'bidang' => $this->db('aset_bidang')->toArray(),
            'unit' => $this->db('aset_unit')->toArray(),
            'unit_bidang' => $this->db('aset_unit')
                ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_unit.kode_bidang')
                ->select(['aset_unit.kode_unit', 'aset_unit.nama_unit', 'aset_bidang.nama_bidang'])
                ->toArray()
        ];
        return $this->draw('distribusi.html', [
            'title' => 'Kelola Distribusi Barang',
            'data' => $data
        ]);
    }

    public function getJenis()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Kelola Jenis Barang';
        $data['jenis'] = $this->db('aset_jenis')->toArray();
        return $this->draw('jenis.html', ['data' => $data]);
    }

    public function postJenis()
    {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1' && isset($_POST['original_kode']) && !empty($_POST['original_kode'])) {
            // Update - cek duplikasi kode jika kode diubah
            if ($_POST['original_kode'] != $_POST['kd_jns_barang']) {
                $existing = $this->db('aset_jenis')->where('kd_jns_barang', $_POST['kd_jns_barang'])->oneArray();
                if ($existing) {
                    $this->notify('failure', 'Kode jenis barang sudah ada');
                    redirect(url([ADMIN, 'asetkami', 'jenis']));
                    return;
                }
            }
            
            $update_data = [
                'kd_jns_barang' => $_POST['kd_jns_barang'],
                'nm_jns_barang' => $_POST['nm_jns_barang']
            ];
            
            if ($this->db('aset_jenis')->where('kd_jns_barang', $_POST['original_kode'])->update($update_data)) {
                $this->notify('success', 'Data jenis barang berhasil diupdate');
            } else {
                $this->notify('failure', 'Gagal mengupdate data jenis barang');
            }
        } else {
            // Insert - cek duplikasi kode
            $existing = $this->db('aset_jenis')->where('kd_jns_barang', $_POST['kd_jns_barang'])->oneArray();
            if ($existing) {
                $this->notify('failure', 'Kode jenis barang sudah ada');
                redirect(url([ADMIN, 'asetkami', 'jenis']));
                return;
            }
            
            $insert_data = [
                'kd_jns_barang' => $_POST['kd_jns_barang'],
                'nm_jns_barang' => $_POST['nm_jns_barang'],
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if ($this->db('aset_jenis')->save($insert_data)) {
                $this->notify('success', 'Data jenis barang berhasil ditambahkan');
            } else {
                $this->notify('failure', 'Gagal menambahkan data jenis barang');
            }
        }
        redirect(url([ADMIN, 'asetkami', 'jenis']));
    }

    public function getDeleteJenis($kd_jns_barang)
    {
        // Cek apakah jenis barang masih digunakan di tabel aset_barang
        $used = $this->db('aset_barang')->where('kd_jns_barang', $kd_jns_barang)->oneArray();
        if ($used) {
            $this->notify('failure', 'Jenis barang tidak dapat dihapus karena masih digunakan');
        } else {
            if ($this->db('aset_jenis')->where('kd_jns_barang', $kd_jns_barang)->delete()) {
                $this->notify('success', 'Data jenis barang berhasil dihapus');
            } else {
                $this->notify('failure', 'Gagal menghapus data jenis barang');
            }
        }
        redirect(url([ADMIN, 'asetkami', 'jenis']));
    }

    public function getDelete_jenis($kd_jns_barang)
    {
        return $this->getDeleteJenis($kd_jns_barang);
    }

    public function getKategori()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Kelola Kategori Barang';
        $data['kategori'] = $this->db('aset_kategori')->toArray();
        return $this->draw('kategori.html', ['data' => $data]);
    }

    public function postKategori()
    {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1') {
            // Update
            // Cek duplikasi kd_kategori untuk record lain
            $existing = $this->db('aset_kategori')
                ->where('kd_kategori', $_POST['kd_kategori'])
                ->where('kd_kategori', '!=', $_POST['original_kode'])
                ->oneArray();
                
            if ($existing) {
                $this->notify('failure', 'Kode kategori sudah ada');
            } else {
                $update_data = [
                    'kd_kategori' => $_POST['kd_kategori'],
                    'nm_kategori_barang' => $_POST['nm_kategori_barang'],
                    'deskripsi' => $_POST['deskripsi']
                ];
                if ($this->db('aset_kategori')->where('kd_kategori', $_POST['original_kode'])->update($update_data)) {
                    $this->notify('success', 'Data kategori barang berhasil diupdate');
                } else {
                    $this->notify('failure', 'Gagal mengupdate data kategori barang');
                }
            }
        } else {
            // Insert
            // Cek duplikasi kd_kategori
            $existing = $this->db('aset_kategori')
                ->where('kd_kategori', $_POST['kd_kategori'])
                ->oneArray();
                
            if ($existing) {
                $this->notify('failure', 'Kode kategori sudah ada');
            } else {
                $insert_data = [
                    'kd_kategori' => $_POST['kd_kategori'],
                    'nm_kategori_barang' => $_POST['nm_kategori_barang'],
                    'deskripsi' => $_POST['deskripsi']
                ];
                if ($this->db('aset_kategori')->save($insert_data)) {
                    $this->notify('success', 'Data kategori barang berhasil ditambahkan');
                } else {
                    $this->notify('failure', 'Gagal menambahkan data kategori barang');
                }
            }
        }
        redirect(url([ADMIN, 'asetkami', 'kategori']));
    }

    public function getDeleteKategori($kd_kategori)
    {
        // Cek apakah kategori masih digunakan di tabel aset_barang
        $used = $this->db('aset_barang')
            ->where('kd_kategori', $kd_kategori)
            ->oneArray();
            
        if ($used) {
            $this->notify('failure', 'Kategori tidak dapat dihapus karena masih digunakan');
        } else {
            if ($this->db('aset_kategori')->where('kd_kategori', $kd_kategori)->delete()) {
                $this->notify('success', 'Data kategori barang berhasil dihapus');
            } else {
                $this->notify('failure', 'Gagal menghapus data kategori barang');
            }
        }
        redirect(url([ADMIN, 'asetkami', 'kategori']));
    }

    public function getDelete_kategori($kd_kategori)
    {
        return $this->getDeleteKategori($kd_kategori);
    }

    public function getUnitbidang()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Kelola Unit per Bidang';
        $data['unitbidang'] = $this->db('aset_unit')
            ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_unit.kode_bidang')
            ->select(['aset_unit.*', 'aset_bidang.nama_bidang'])
            ->toArray();
        $data['bidang'] = $this->db('aset_bidang')->toArray();
        return $this->draw('unitbidang.html', ['data' => $data]);
    }

    public function postUnitbidang()
    {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1') {
            // Update
            // Cek duplikasi kombinasi kode_bidang + kode_unit untuk record lain
            $existing = $this->db('aset_unit')
                ->where('kode_bidang', $_POST['kode_bidang'])
                ->where('kode_unit', $_POST['kode_unit'])
                ->where('kode_unit', '!=', $_POST['original_kode'])
                ->oneArray();
                
            if ($existing) {
                $this->notify('failure', 'Kombinasi kode bidang dan kode unit sudah ada');
            } else {
                $update_data = [
                    'kode_bidang' => $_POST['kode_bidang'],
                    'kode_unit' => $_POST['kode_unit'],
                    'nama_unit' => $_POST['nama_unit']
                ];
                if ($this->db('aset_unit')
                    ->where('kode_bidang', $_POST['original_bidang'])
                    ->where('kode_unit', $_POST['original_kode'])
                    ->update($update_data)) {
                    $this->notify('success', 'Data unit per bidang berhasil diupdate');
                } else {
                    $this->notify('failure', 'Gagal mengupdate data unit per bidang');
                }
            }
        } else {
            // Insert
            // Cek duplikasi kombinasi kode_bidang + kode_unit
            $existing = $this->db('aset_unit')
                ->where('kode_bidang', $_POST['kode_bidang'])
                ->where('kode_unit', $_POST['kode_unit'])
                ->oneArray();
                
            if ($existing) {
                $this->notify('failure', 'Kombinasi kode bidang dan kode unit sudah ada');
            } else {
                $insert_data = [
                    'kode_bidang' => $_POST['kode_bidang'],
                    'kode_unit' => $_POST['kode_unit'],
                    'nama_unit' => $_POST['nama_unit']
                ];
                if ($this->db('aset_unit')->save($insert_data)) {
                    $this->notify('success', 'Data unit per bidang berhasil ditambahkan');
                } else {
                    $this->notify('failure', 'Gagal menambahkan data unit per bidang');
                }
            }
        }
        redirect(url([ADMIN, 'asetkami', 'unitbidang']));
    }

    public function getDeleteUnitbidang($kode_bidang, $kode_unit)
    {
        // Cek apakah unit masih digunakan di tabel lain
        $used_distribusi = $this->db('aset_distribusi')
            ->where('kode_unit', $kode_unit)
            ->oneArray();
            
        if ($used_distribusi) {
            $this->notify('failure', 'Unit tidak dapat dihapus karena masih digunakan dalam distribusi');
        } else {
            if ($this->db('aset_unit')
                ->where('kode_bidang', $kode_bidang)
                ->where('kode_unit', $kode_unit)
                ->delete()) {
                $this->notify('success', 'Data unit per bidang berhasil dihapus');
            } else {
                $this->notify('failure', 'Gagal menghapus data unit per bidang');
            }
        }
        redirect(url([ADMIN, 'asetkami', 'unitbidang']));
    }

    public function getDelete_unitbidang($kode_bidang, $kode_unit)
    {
        return $this->getDeleteUnitbidang($kode_bidang, $kode_unit);
    }

    public function getData()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Data Aset';
        
        // Hitung total aset
        $total_aset = $this->db('aset_barang')->count();
        $data['total_aset'] = $total_aset;
        
        // Query untuk menampilkan semua data barang dengan prioritas status dari aset_barang
        $sql = "SELECT 
            ab.id,
            ab.kode_barang,
            ab.kd_jns_barang,
            ab.kd_kategori,
            ab.merk,
            ab.model_seri,
            ab.sn,
            ab.tanggal_input,
            ab.tanggal_barang,
            ab.petugas as petugas_barang,
            ab.sumber_dana,
            ab.asal_barang,
            ab.foto,
            ak.nm_kategori_barang, 
            aj.nm_jns_barang,
            ad.kd_distribusi,
            ad.tanggal_distribusi,
            ad.tanggal_pengadaan as tgl_pengadaan_dist,
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
        ORDER BY (ad.tanggal_distribusi IS NULL) ASC, ad.tanggal_distribusi DESC, ab.id DESC";
        
        $data['aset'] = $this->db()->pdo()->query($sql)->fetchAll();
        
        // Ambil data bidang untuk dropdown
        $data['bidang'] = $this->db('aset_bidang')
            ->select(['kode_bidang', 'nama_bidang'])
            ->toArray();
            
        // Ambil data unit untuk dropdown ruangan tujuan
        $data['unit'] = $this->db('aset_unit')
            ->select(['kode_unit', 'nama_unit', 'kode_bidang'])
            ->toArray();
        $data['unit_bidang'] = $this->db('aset_unit')
            ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_unit.kode_bidang')
            ->select(['aset_unit.kode_unit', 'aset_unit.nama_unit', 'aset_bidang.nama_bidang'])
            ->toArray();
            
        // Ambil data kategori untuk dropdown filter
        $data['kategori'] = $this->db('aset_kategori')
            ->select(['kd_kategori', 'nm_kategori_barang'])
            ->toArray();
        
        // Format data sesuai konsep baru
        foreach ($data['aset'] as &$aset) {
            $tgl_pengadaan = !empty($aset['tanggal_barang']) && $aset['tanggal_barang'] != '0000-00-00' ? $aset['tanggal_barang'] : null;
            
            // Fallback ke data distribusi jika kosong
            if (empty($tgl_pengadaan) && !empty($aset['tgl_pengadaan_dist']) && $aset['tgl_pengadaan_dist'] != '0000-00-00') {
                $tgl_pengadaan = $aset['tgl_pengadaan_dist'];
            }
            
            $aset['tanggal_pengadaan'] = !empty($tgl_pengadaan) ? date('d/m/Y', strtotime($tgl_pengadaan)) : '-';

            // Tanggal distribusi
            if ($aset['status'] == 'terdistribusi' && !empty($aset['tanggal_distribusi'])) {
                $aset['tanggal_distribusi'] = date('d/m/Y', strtotime($aset['tanggal_distribusi']));
            } else {
                $aset['tanggal_distribusi'] = '-';
            }
            
            if ($aset['status'] == 'terdistribusi') {
                // Jika barang sudah terdistribusi, ambil data lokasi dari aset_distribusi
                // Tampilkan nama_unit dari join dengan aset_unit, fallback ke kode_unit jika tidak ada
                if (!empty($aset['nama_unit'])) {
                    $aset['lokasi'] = $aset['nama_bidang'] . ' - ' . $aset['nama_unit'];
                } else {
                    $aset['lokasi'] = $aset['kode_unit'] ?? 'Unit tidak diketahui';
                }
                // Gunakan fullname jika tersedia, fallback ke username
                $aset['petugas'] = $aset['fullname_petugas_distribusi'] ?? $aset['petugas_distribusi'];
            } else {
                // Jika barang belum terdistribusi, ambil data dari aset_barang
                // Tampilkan kd_distribusi jika ada, walaupun barang di gudang
                $aset['kd_distribusi'] = $aset['kd_distribusi'] ?? '-';
                $aset['lokasi'] = 'Gudang';
                // Gunakan fullname jika tersedia, fallback ke username
                $aset['petugas'] = $aset['fullname_petugas_barang'] ?? $aset['petugas_barang'];
            }
        }
        
        return $this->draw('data.html', ['data' => $data]);
    }

    public function postEdit()
    {
        if (isset($_POST['id']) && !empty($_POST['id'])) {
            $update_data = [
                // 'tanggal_input' => $_POST['tanggal_input'],
                'merk' => $_POST['merk'],
                'model_seri' => $_POST['model_seri'],
                'sn' => $_POST['sn'],
                'stok' => $_POST['stok'],
                'tanggal_barang' => $_POST['tanggal_barang'],
                'kelengkapan' => $_POST['kelengkapan'],
                'sumber_dana' => $_POST['sumber_dana'],
                'asal_barang' => $_POST['asal_barang'],
                'status_verifikasi' => $_POST['status_verifikasi']
            ];
            
            // Handle file upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
                $upload_dir = BASE_DIR . '/uploads/aset/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $new_filename = time() . '_' . $_FILES['foto']['name'];
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                    $update_data['foto'] = $new_filename;
                }
            }
            
            if ($this->db('aset_barang')->where('id', $_POST['id'])->update($update_data)) {
                $this->notify('success', 'Data barang berhasil diupdate');
            } else {
                $this->notify('failure', 'Gagal mengupdate data barang');
            }
        }
        
        // Redirect back to detail page
        $barang = $this->db('aset_barang')->where('id', $_POST['id'])->oneArray();
        if ($barang) {
            redirect(url([ADMIN, 'asetkami', 'detail', $barang['kd_jns_barang']]));
        } else {
            redirect(url([ADMIN, 'asetkami', 'gudang']));
        }
    }
    
    public function getHapus($id)
    {
        $barang = $this->db('aset_barang')->where('id', $id)->oneArray();
        
        if ($barang) {
            // Delete photo file if exists
            if (!empty($barang['foto'])) {
                $photo_path = BASE_DIR . '/uploads/aset/' . $barang['foto'];
                if (file_exists($photo_path)) {
                    unlink($photo_path);
                }
            }
            
            // Delete from database
            if ($this->db('aset_barang')->where('id', $id)->delete()) {
                $this->notify('success', 'Data barang berhasil dihapus');
            } else {
                $this->notify('failure', 'Gagal menghapus data barang');
            }
            
            redirect(url([ADMIN, 'asetkami', 'detail', $barang['kd_jns_barang']]));
        } else {
            $this->notify('failure', 'Data barang tidak ditemukan');
            redirect(url([ADMIN, 'asetkami', 'gudang']));
        }
    }


    public function postPindah_tangan()
    {
        header('Content-Type: application/json');
        
        try {
            $barang_id = $_POST['barang_id'];
            $kode_bidang = $_POST['kode_bidang'];
            $kode_unit = $_POST['kode_unit'];
            $keterangan = $_POST['keterangan'] ?? '';
            
            // Ambil data barang
            $barang = $this->db('aset_barang')->where('id', $barang_id)->oneArray();
            if (!$barang) {
                echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
                exit;
            }
            
            // Ambil distribusi saat ini
            $distribusi_lama = $this->db('aset_distribusi')
                ->where('kode_barang', $barang['kode_barang'])
                ->oneArray();
            
            if (!$distribusi_lama) {
                echo json_encode(['status' => 'error', 'message' => 'Barang belum terdistribusi']);
                exit;
            }
            
            // Tambahkan record distribusi baru untuk pindah tangan
            // Ini akan membuat riwayat distribusi yang lengkap
            
            // Generate kd_distribusi yang unik dengan format yang konsisten
            // Format: intra_ektra + sumber + template + bidang + unit + tahun + urutan_unik
            $template = '25080701'; // Kalsel(25) + HST(08) + Kesehatan(07) + RSUD(01)
            $tahun = date('Y');
            $urutan = substr(time(), -4) . rand(10, 99); // timestamp 4 digit + random 2 digit
            
            // Untuk pindah tangan, gunakan format standar dengan prefix khusus
            $kd_distribusi = '0111' . $template . $kode_bidang . $kode_unit . $tahun . $urutan;
            
            $insert_data = [
                'kd_distribusi' => $kd_distribusi,
                'kode_barang' => $barang['kode_barang'],
                'kode_bidang' => $kode_bidang,
                'kode_unit' => $kode_unit,
                'tanggal_distribusi' => date('Y-m-d H:i:s'),
                'petugas' => $this->core->getUserInfo('username', null, true),
                'keterangan' => 'Pindah tangan: ' . $keterangan
            ];
            
            $this->db('aset_distribusi')->save($insert_data);
            
            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dipindah tangan']);
            
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function postReturn_barang()
    {
        header('Content-Type: application/json');
        
        try {
            $barang_id = $_POST['barang_id'];
            $keterangan = $_POST['keterangan'] ?? 'Return ke gudang';
            
            // Ambil data barang
            $barang = $this->db('aset_barang')->where('id', $barang_id)->oneArray();
            if (!$barang) {
                echo json_encode(['status' => 'error', 'message' => 'Barang tidak ditemukan']);
                exit;
            }
            
            // Pastikan barang sedang terdistribusi (status = 2)
            if ($barang['status'] != '2') {
                echo json_encode(['status' => 'error', 'message' => 'Barang tidak sedang terdistribusi']);
                exit;
            }
            
            // Ambil distribusi terakhir untuk mendapatkan kd_distribusi
            $distribusi_terakhir = $this->db('aset_distribusi')
                ->where('kode_barang', $barang['kode_barang'])
                ->desc('tanggal_distribusi')
                ->oneArray();
            
            if (!$distribusi_terakhir) {
                echo json_encode(['status' => 'error', 'message' => 'Data distribusi tidak ditemukan']);
                exit;
            }
            
            // Gunakan kd_distribusi yang sudah ada dari distribusi terakhir
            $kd_distribusi_existing = $distribusi_terakhir['kd_distribusi'];
            
            // Insert record return baru ke aset_distribusi
            $return_data = [
                'kd_distribusi' => $kd_distribusi_existing, // gunakan kd_distribusi sebelumnya
                'kode_barang' => $barang['kode_barang'], // tetap sama
                'tanggal_distribusi' => date('Y-m-d H:i:s'), // tanggal return
                'petugas' => $this->core->getUserInfo('username', null, true), // user yang return
                'keterangan' => 'Return ke gudang: ' . $keterangan,
                'kode_bidang' => null,
                'kode_unit' => null
            ];
            
            $this->db('aset_distribusi')->save($return_data);
            
            // Update status barang menjadi '1' (di gudang) dan tambah stok
            $update_data = [
                'status' => '1',
                'stok' => $barang['stok'] + 1
            ];
            
            $this->db('aset_barang')->where('id', $barang_id)->update($update_data);
            
            echo json_encode(['status' => 'success', 'message' => 'Barang berhasil dikembalikan ke gudang']);
            
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
        exit;
    }
    
    public function getAnyRiwayat()
    {
        return $this->getRiwayat();
    }
    
    public function getRiwayat()
    {
        $kode_barang = $_GET['kode_barang'] ?? '';
        
        if (empty($kode_barang)) {
            echo '<div class="alert alert-danger">Kode Barang tidak valid</div>';
            exit;
        }
        
        // Ambil data barang dengan join ke mlite_users
        $barang = $this->db('aset_barang')
            ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang')
            ->join('mlite_users', 'mlite_users.username=aset_barang.petugas', 'LEFT')
            ->select(['aset_barang.*', 'aset_jenis.nm_jns_barang', 'mlite_users.fullname as petugas_fullname'])
            ->where('aset_barang.kode_barang', $kode_barang)
            ->oneArray();
            
        if (!$barang) {
            echo '<div class="alert alert-danger">Barang tidak ditemukan</div>';
            exit;
        }
        
        // Ambil riwayat distribusi dari tabel aset_distribusi dengan join ke mlite_users
        $distribusi = $this->db('aset_distribusi')
            ->join('mlite_users', 'mlite_users.username=aset_distribusi.petugas', 'LEFT')
            ->select(['aset_distribusi.*', 'mlite_users.fullname as petugas_fullname'])
            ->where('aset_distribusi.kode_barang', $barang['kode_barang'])
            ->toArray();
        
        // Tambahkan riwayat penginputan ke gudang sebagai entri pertama
        $riwayat = [];
        $riwayat_input = [
            'kd_inventaris' => 'INV-' . $barang['kode_barang'],
            'kd_jns_barang' => $barang['kd_jns_barang'],
            'dari_unit' => null,
            'ke_unit' => 'GUDANG',
            'tanggal_pindah' => $barang['tanggal_input'],
            'keterangan' => 'Input barang ke gudang - ' . ($barang['asal_barang'] ?? 'Tidak diketahui'),
            'user_id' => $barang['petugas_fullname'] ?? $barang['petugas'] ?? 'System',
            'status' => 'input_gudang'
        ];
        $riwayat[] = $riwayat_input;
        
        // Ambil mapping unit untuk nama dari tabel aset_unit dan aset_bidang
        $units = $this->db('aset_unit')
            ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_unit.kode_bidang')
            ->select(['aset_unit.kode_unit', 'aset_unit.nama_unit', 'aset_bidang.nama_bidang', 'aset_unit.kode_bidang'])
            ->toArray();
        $unit_map = [];
        foreach ($units as $unit) {
            // Gunakan kombinasi kode_bidang dan kode_unit sebagai key
            $key = $unit['kode_bidang'] . '_' . $unit['kode_unit'];
            $unit_map[$key] = $unit['nama_bidang'] . ' - ' . $unit['nama_unit'];
            // Juga simpan dengan kode_unit saja untuk backward compatibility
            $unit_map[$unit['kode_unit']] = $unit['nama_bidang'] . ' - ' . $unit['nama_unit'];
        }
        
        // Tambahkan riwayat distribusi dengan logika ruangan sebelumnya
        foreach ($distribusi as $index => $dist) {
            // Cek apakah ini adalah aksi return berdasarkan keterangan
            $is_return = strpos($dist['keterangan'], 'Return ke gudang') !== false;
            
            // Tentukan ruangan asal (dari_unit)
            if ($index == 0 && !$is_return) {
                // Distribusi pertama selalu dari GUDANG
                $dari_unit = 'GUDANG';
                $status = 'distribusi';
            } else {
                // Distribusi selanjutnya dari ruangan sebelumnya (pindah tangan atau return)
                $prev_dist = $distribusi[$index - 1];
                $dari_unit_key = $prev_dist['kode_bidang'] . '_' . $prev_dist['kode_unit'];
                $dari_unit = $unit_map[$dari_unit_key] ?? ($unit_map[$prev_dist['kode_unit']] ?? $prev_dist['kode_unit']);
                
                if ($is_return) {
                    $status = 'return';
                } else {
                    $status = 'pindah_tangan';
                }
            }
            
            // Mapping nama unit untuk ke_unit
            if ($is_return) {
                $ke_unit_nama = 'GUDANG';
            } else {
                $key = $dist['kode_bidang'] . '_' . $dist['kode_unit'];
                $ke_unit_nama = $unit_map[$key] ?? $dist['kode_unit'];
            }
            
            $riwayat[] = [
                'kd_inventaris' => 'INV-' . $barang['kode_barang'],
                'kd_jns_barang' => $barang['kd_jns_barang'],
                'dari_unit' => $dari_unit,
                'ke_unit' => $ke_unit_nama,
                'kode_bidang' => $dist['kode_bidang'],
                'kode_unit_raw' => $dist['kode_unit'],
                'tanggal_pindah' => $dist['tanggal_distribusi'],
                'keterangan' => $dist['keterangan'] ?? ($status == 'distribusi' ? 'Distribusi barang' : ($status == 'return' ? 'Return ke gudang' : 'Pindah tangan')),
                'user_id' => $dist['petugas_fullname'] ?? $dist['petugas'] ?? 'System',
                'status' => $status
            ];
        }
        
        // Riwayat sudah lengkap dari aset_distribusi dan input gudang
        
        // Urutkan berdasarkan tanggal
        usort($riwayat, function($a, $b) {
            return strtotime($a['tanggal_pindah']) - strtotime($b['tanggal_pindah']);
        });
        
        // Unit mapping sudah dibuat di atas, tidak perlu duplikasi
        
        $html = '<div class="panel panel-default">';
        $html .= '<div class="panel-heading"><strong>Informasi Barang</strong></div>';
        $html .= '<div class="panel-body">';
        $html .= '<p><strong>Jenis:</strong> ' . $barang['nm_jns_barang'] . '</p>';
        $html .= '<p><strong>Merk:</strong> ' . $barang['merk'] . '</p>';
        $html .= '<p><strong>Model/Seri:</strong> ' . $barang['model_seri'] . '</p>';
        $html .= '<p><strong>SN:</strong> ' . $barang['sn'] . '</p>';
        $html .= '</div></div>';
        
        $html .= '<div class="panel panel-default">';
        $html .= '<div class="panel-heading"><strong>Riwayat Distribusi</strong></div>';
        $html .= '<div class="panel-body">';
        
        if (empty($riwayat)) {
            $html .= '<p class="text-muted">Belum ada riwayat distribusi untuk barang ini.</p>';
        } else {
            $html .= '<div class="timeline">';
            foreach ($riwayat as $index => $item) {
                // Untuk 'dari_unit', gunakan mapping jika tersedia
                $dari = $item['dari_unit'] ? ($unit_map[$item['dari_unit']] ?? $item['dari_unit']) : 'Gudang';
                
                // Untuk 'ke_unit', gunakan kombinasi kode_bidang dan kode_unit jika tersedia
                if ($item['ke_unit'] == 'GUDANG') {
                    $ke = 'Gudang';
                } else {
                    if (isset($item['kode_bidang']) && isset($item['kode_unit_raw'])) {
                        $key = $item['kode_bidang'] . '_' . $item['kode_unit_raw'];
                        $ke = $unit_map[$key] ?? $item['ke_unit'];
                    } else {
                        $ke = $item['ke_unit'];
                    }
                }
                $tanggal = date('d/m/Y H:i', strtotime($item['tanggal_pindah']));
                
                // Tentukan badge dan text berdasarkan status
                if ($item['status'] == 'input_gudang') {
                    $status_badge = 'info';
                    $status_text = 'Input ke Gudang';
                } elseif ($item['status'] == 'distribusi') {
                    $status_badge = 'success';
                    $status_text = 'Distribusi';
                } elseif ($item['status'] == 'pindah_tangan') {
                    $status_badge = 'warning';
                    $status_text = 'Pindah Tangan';
                } elseif ($item['status'] == 'return') {
                    $status_badge = 'danger';
                    $status_text = 'Return';
                } else {
                    $status_badge = 'secondary';
                    $status_text = 'Lainnya';
                }
                
                $html .= '<div class="timeline-item">';
                $html .= '<div class="timeline-marker"><span class="badge badge-' . $status_badge . '">' . ($index + 1) . '</span></div>';
                $html .= '<div class="timeline-content">';
                $html .= '<h5><span class="badge badge-' . $status_badge . '">' . $status_text . '</span></h5>';
                
                // Tampilkan format khusus untuk input gudang
                if ($item['status'] == 'input_gudang') {
                    $html .= '<p><strong>Pemasukan barang ke Gudang</strong></p>';
                } else {
                    $html .= '<p><strong>Dari:</strong> ' . $dari . ' <strong>→ Ke:</strong> ' . $ke . '</p>';
                }
                $html .= '<p><strong>Waktu:</strong> ' . $tanggal . '</p>';
                if (!empty($item['keterangan'])) {
                    $html .= '<p><strong>Keterangan:</strong> ' . $item['keterangan'] . '</p>';
                }
                $html .= '<p><strong>User:</strong> ' . $item['user_id'] . '</p>';
                $html .= '</div></div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div></div>';
        
        // Add CSS for timeline
        $html .= '<style>
        .timeline { position: relative; padding-left: 30px; }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-marker { position: absolute; left: -30px; top: 0; }
        .timeline-content { background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 3px solid #007bff; }
        </style>';
        
        echo $html;
        exit;
    }

    public function getBidang()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Kelola Data Bidang';
        $data['bidang'] = $this->db('aset_bidang')->toArray();
        return $this->draw('bidang.html', ['data' => $data]);
    }

    public function postBidang()
    {
        if (isset($_POST['edit_mode']) && $_POST['edit_mode'] == '1') {
            // Update
            $update_data = [
                'kode_bidang' => $_POST['kode_bidang'],
                'nama_bidang' => $_POST['nama_bidang']
            ];
            if ($this->db('aset_bidang')->where('kode_bidang', $_POST['original_kode'])->update($update_data)) {
                $this->notify('success', 'Data bidang berhasil diupdate');
            } else {
                $this->notify('failure', 'Gagal mengupdate data bidang');
            }
        } else {
            // Insert
            // Cek duplikasi kode bidang
            $existing = $this->db('aset_bidang')
                ->where('kode_bidang', $_POST['kode_bidang'])
                ->oneArray();
                
            if ($existing) {
                $this->notify('failure', 'Kode bidang sudah ada');
            } else {
                $insert_data = [
                    'kode_bidang' => $_POST['kode_bidang'],
                    'nama_bidang' => $_POST['nama_bidang'],
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if ($this->db('aset_bidang')->save($insert_data)) {
                    $this->notify('success', 'Data bidang berhasil ditambahkan');
                } else {
                    $this->notify('failure', 'Gagal menambahkan data bidang');
                }
            }
        }
        redirect(url([ADMIN, 'asetkami', 'bidang']));
    }

    public function getHapus_Bidang($kode_bidang)
    {
        if ($this->db('aset_bidang')->where('kode_bidang', $kode_bidang)->delete()) {
            $this->notify('success', 'Data bidang berhasil dihapus');
        } else {
            $this->notify('failure', 'Gagal menghapus data bidang');
        }
        redirect(url([ADMIN, 'asetkami', 'bidang']));
    }

    // public function getApiBidang()
    // {
    //     header('Content-Type: application/json');
        
    //     $bidang = $this->db('aset_bidang')
    //         ->toArray();
        
    //     echo json_encode($bidang);
    //     exit;
    // }


    
    public function getKir()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'KIR (Kartu Inventaris Aset)';
        
        // Ambil data bidang untuk dropdown
        $data['aset_bidang'] = $this->db('aset_bidang')
            ->select(['kode_bidang', 'nama_bidang'])
            ->toArray();
            
        // Ambil data unit untuk dropdown
        $data['aset_unit'] = $this->db('aset_unit')
            ->select(['kode_unit', 'nama_unit', 'kode_bidang'])
            ->toArray();
            
        return $this->draw('kir.html', ['data' => $data]);
    }

    public function getKirData()
    {
        header('Content-Type: application/json');
        
        $kode_bidang = $_GET['kode_bidang'] ?? '';
        $kode_unit = $_GET['kode_unit'] ?? '';
        
        if (empty($kode_bidang) || empty($kode_unit)) {
            echo json_encode(['error' => 'Bidang dan Unit/Ruangan harus dipilih']);
            exit;
        }
        
        // Ambil data barang yang ada di bidang dan unit tertentu dari tabel aset_distribusi
        // Menggunakan subquery untuk mendapatkan distribusi terbaru per barang
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
            ad.keterangan,
            (SELECT status FROM aset_pengajuan_kembali apk WHERE apk.kd_distribusi = ad.kd_distribusi ORDER BY apk.id DESC LIMIT 1) as status_pengajuan
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
        
        $barang = $this->db()->pdo()->prepare($sql);
        $barang->execute([$kode_bidang, $kode_unit]);
        $barang = $barang->fetchAll();
        
        // Format data untuk tampilan
        $result = [];
        foreach ($barang as $item) {
            $tombolAjukan = '';
            $status_pengajuan = $item['status_pengajuan'] ?? '';
            
            // Tampilkan tombol ajukan hanya jika belum ada pengajuan pending/setuju
            if ($status_pengajuan != 'pending' && $status_pengajuan != 'setuju') {
                $tombolAjukan = ' <button class="btn btn-primary btn-xs" style="margin-top:2px;" onclick="pengajuanReturnBarang(\''.$item['kd_distribusi'].'\', \''.$item['kode_barang'].'\', \''.addslashes($item['merk']).' '.addslashes($item['model_seri']).'\')"><i class="fa fa-paper-plane"></i> Ajukan</button>';
            }

            $result[] = [
                'id' => $item['id'],
                'kode_barang' => $item['kode_barang'],
                'nm_jns_barang' => $item['nm_jns_barang'],
                'nm_kategori_barang' => $item['nm_kategori_barang'],
                'merk' => $item['merk'],
                'model_seri' => $item['model_seri'],
                'sn' => $item['sn'],
                'kelengkapan' => $item['kelengkapan'],
                'harga' => $item['harga'],
                'tanggal_input' => date('d/m/Y', strtotime($item['tanggal_input'])),
                'kode_bidang' => $item['kode_bidang'],
                'kode_unit' => $item['kode_unit'],
                'tanggal_distribusi' => $item['tanggal_distribusi'] ? date('d/m/Y', strtotime($item['tanggal_distribusi'])) : '-',
                'kd_distribusi' => $item['kd_distribusi'],
                'petugas' => $item['petugas'],
                'keterangan' => $item['keterangan'],
                'harga_format' => 'Rp ' . number_format($item['harga'], 0, ',', '.'),
                'aksi' => '<button class="btn btn-warning btn-xs" onclick="returnBarang(\''.$item['id'].'\', \''.addslashes($item['merk']).'\', \''.addslashes($item['model_seri']).'\')"><i class="fa fa-reply"></i> Return</button>' . $tombolAjukan
            ];
        }
        $barang = $result;
        
        echo json_encode($barang);
        exit;
    }
    
    public function getApiunit()
    {
        header('Content-Type: application/json');
        
        $kode_bidang = $_GET['kode_bidang'] ?? '';
        
        if (empty($kode_bidang)) {
            echo json_encode([]);
            exit;
        }
        
        $units = $this->db('aset_unit')
            ->where('kode_bidang', $kode_bidang)
            ->select(['kode_unit', 'nama_unit'])
            ->toArray();
        
        echo json_encode($units);
        exit;
    }
    
    public function getUnitByBidang()
    {
        header('Content-Type: application/json');
        
        try {
            $kode_bidang = $_GET['kode_bidang'] ?? '';
            
            if (empty($kode_bidang)) {
                echo json_encode(['status' => 'error', 'message' => 'Kode bidang tidak boleh kosong']);
                exit;
            }
            
            $units = $this->db('aset_unit')
                ->where('kode_bidang', $kode_bidang)
                ->select(['kode_unit', 'nama_unit'])
                ->toArray();
            
            echo json_encode(['status' => 'success', 'data' => $units]);
            
        } catch (\Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
        exit;
    }


    public function getScanner()
    {
        $this->_addHeaderFiles();
        $data['title'] = 'Scanner QR Code';
        return $this->draw('scanner.html', ['data' => $data]);
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
                ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit AND aset_unit.kode_bidang=aset_distribusi.kode_bidang')
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
                
                echo json_encode(['success' => true, 'data' => $distribusi]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Barang dengan kode distribusi "' . $kd_distribusi . '" tidak ditemukan']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit;
    }

    public function postDecode()
    {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $kd_distribusi = $input['kd_distribusi'] ?? '';
        
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
                ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit AND aset_unit.kode_bidang=aset_distribusi.kode_bidang')
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
                
                echo json_encode(['success' => true, 'data' => $distribusi]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Barang dengan kode distribusi "' . $kd_distribusi . '" tidak ditemukan']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        
        exit;
    }


    public function postSimpanPengajuan()
    {
        header('Content-Type: application/json');

        try {
            $kd_distribusi = $_POST['kd_distribusi'] ?? '';
            $kode_barang  = $_POST['kode_barang'] ?? '';
            $kondisi      = $_POST['kondisi'] ?? '';
            $alasan       = $_POST['alasan'] ?? '';

            if ($kd_distribusi === '' || $kode_barang === '' || $kondisi === '' || $alasan === '') {
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'Data pengajuan tidak lengkap'
                ]);
                exit;
            }

            $foto = '';
            if (isset($_FILES['foto']) && isset($_FILES['foto']['tmp_name']) && $_FILES['foto']['error'] === 0) {
                $target_dir = UPLOADS . '/aset/pengajuan';
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0755, true);
                }

                $ext      = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $filename = uniqid('pengajuan_', true) . '.' . $ext;
                $target_file = $target_dir . '/' . $filename;

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                    $foto = 'aset/pengajuan/' . $filename;
                }
            }

            $save = $this->db('aset_pengajuan_kembali')->save([
                'kd_distribusi'       => $kd_distribusi,
                'kode_barang'         => $kode_barang,
                'foto'                => $foto,
                'kondisi_barang'      => $kondisi,
                'alasan_pengembalian' => $alasan,
                'tanggal_pengajuan'   => date('Y-m-d H:i:s'),
                'status'              => 'pending'
            ]);

            if ($save) {
                echo json_encode(['status' => 'success', 'message' => 'Pengajuan berhasil disimpan']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data pengajuan']);
            }
        } catch (\Exception $e) {
            echo json_encode([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    public function getKembali()
    {
        $this->_addHeaderFiles();

        return $this->draw('kembali.html', [
            'title' => 'Data Pengembalian Barang'
        ]);
    }

    public function getKembaliData()
    {
        header('Content-Type: application/json');
        
        $mode = isset($_GET['mode']) ? $_GET['mode'] : 'data';

        $query = $this->db('aset_pengajuan_kembali')
            ->join('aset_barang', 'aset_barang.kode_barang=aset_pengajuan_kembali.kode_barang', 'LEFT')
            ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang', 'LEFT')
            ->select([
                'aset_pengajuan_kembali.*',
                'aset_jenis.nm_jns_barang',
                'aset_barang.merk',
                'aset_barang.model_seri'
            ]);

        if ($mode == 'verifikasi') {
            $query->where('aset_pengajuan_kembali.status', 'pending');
        } else {
            $query->where('aset_pengajuan_kembali.status', '<>', 'pending');
        }

        $data = $query->desc('aset_pengajuan_kembali.id')
            ->toArray();
            
        $output = [];
        foreach ($data as $item) {
            $statusBadge = '';
            if ($item['status'] == 'pending') {
                $statusBadge = '<span class="label label-warning">Pending</span>';
            } elseif ($item['status'] == 'setuju') {
                $statusBadge = '<span class="label label-success">Disetujui</span>';
            } else {
                $statusBadge = '<span class="label label-danger">Ditolak</span>';
            }
            
            $aksi = '';
            if ($item['status'] == 'pending') {
                $aksi .= '<div class="btn-group btn-group-xs">';
                $aksi .= '<button class="btn btn-success" onclick="verifikasi(\''.$item['id'].'\', \'setuju\')"><i class="fa fa-check"></i></button>';
                $aksi .= '<button class="btn btn-danger" onclick="verifikasi(\''.$item['id'].'\', \'tolak\')"><i class="fa fa-times"></i></button>';
                $aksi .= '</div>';
            } elseif ($item['status'] == 'setuju') {
                $aksi .= '<a href="'.url([ADMIN, 'asetkami', 'cetak', $item['id']]).'" target="_blank" class="btn btn-default btn-xs"><i class="fa fa-print"></i> Cetak Surat</a>';
            }

            $fotoLink = '-';
            if (!empty($item['foto'])) {
                $fotoUrl = url('uploads/'.$item['foto']);
                $fotoLink = '<a href="'.$fotoUrl.'" target="_blank" class="btn btn-default btn-xs">Lihat</a>';
            }
            
            $output[] = [
                'kd_distribusi' => $item['kd_distribusi'],
                'kode_barang' => $item['kode_barang'],
                'nama_barang' => $item['nm_jns_barang'] . ' ' . $item['merk'] . ' ' . $item['model_seri'],
                'tanggal' => date('d/m/Y H:i', strtotime($item['tanggal_pengajuan'])),
                'foto' => $fotoLink,
                'kondisi' => $item['kondisi_barang'],
                'alasan' => $item['alasan_pengembalian'],
                'status' => $statusBadge,
                'aksi' => $aksi
            ];
        }
        
        echo json_encode(['data' => $output]);
        exit;
    }

    public function postVerifikasiKembali()
    {
        $id = $_POST['id'];
        $status = $_POST['status'];
        
        $this->db('aset_pengajuan_kembali')
            ->where('id', $id)
            ->save([
                'status' => $status,
                'tanggal_verifikasi' => date('Y-m-d H:i:s'),
                'verifikator' => $this->core->getUserInfo('fullname', null, true)
            ]);
            
        echo json_encode(['status' => 'success']);
        exit;
    }

    public function getCetak($id)
    {
        $pengajuan = $this->db('aset_pengajuan_kembali')
            ->join('aset_barang', 'aset_barang.kode_barang=aset_pengajuan_kembali.kode_barang', 'LEFT')
            ->join('aset_jenis', 'aset_jenis.kd_jns_barang=aset_barang.kd_jns_barang', 'LEFT')
            ->join('aset_distribusi', 'aset_distribusi.kd_distribusi=aset_pengajuan_kembali.kd_distribusi', 'LEFT')
            ->join('aset_unit', 'aset_unit.kode_unit=aset_distribusi.kode_unit AND aset_unit.kode_bidang=aset_distribusi.kode_bidang', 'LEFT')
            ->join('aset_bidang', 'aset_bidang.kode_bidang=aset_distribusi.kode_bidang', 'LEFT')
            ->where('aset_pengajuan_kembali.id', $id)
            ->oneArray();
            
        if (!$pengajuan) {
            echo "Data tidak ditemukan";
            exit;
        }

        if (ob_get_length()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
        }
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="surat-pengembalian-'.$id.'.pdf"');

        $pdf = new FPDF();
        $pdf->AddPage();
        $logoPath = __DIR__ . '/../asetkami/img/logobrb.png';
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 10, 25);
        }

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,5,'PEMERINTAH KABUPATEN HULU SUNGAI TENGAH',0,1,'C');
        $pdf->Cell(0,5,'DINAS KESEHATAN',0,1,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,6,'UPT RSUD H. DAMANHURI BARABAI',0,1,'C');
        $pdf->SetFont('Arial','',9);
        $pdf->Cell(0,4,'Jalan Murakata Nomor 4 Barabai Barat, Hulu Sungai Tengah, Kalimantan Selatan 71314',0,1,'C');
        $pdf->Cell(0,4,'Telepon : 0811500800, Laman www.rshdbarabai.com, Pos-e rshd@hstkab.go.id',0,1,'C');

        $pdf->Ln(4);
        $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
        $pdf->Ln(6);

        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(0,7,'SURAT PENGEMBALIAN BARANG',0,1,'C');
        $pdf->Ln(6);

        $pdf->SetFont('Arial','',11);
        $leftMargin = 25;
        $labelWidth = 40;

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Tanggal',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->Cell(0,6,date('d F Y', strtotime($pengajuan['tanggal_verifikasi'])),0,1);

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Unit Asal',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->Cell(0,6,($pengajuan['nama_bidang']??'-').' - '.($pengajuan['nama_unit']??'-'),0,1);

        $pdf->Ln(6);
        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Barang yang dikembalikan',0,0);
        $pdf->Cell(5,6,':',0,1);

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Kode Barang',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->Cell(0,6,$pengajuan['kode_barang'],0,1);

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Nama Barang',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->Cell(0,6,($pengajuan['nm_jns_barang']??'').' '.($pengajuan['merk']??'').' '.($pengajuan['model_seri']??''),0,1);

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Kondisi',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->Cell(0,6,$pengajuan['kondisi_barang'],0,1);

        $pdf->SetX($leftMargin);
        $pdf->Cell($labelWidth,6,'Alasan',0,0);
        $pdf->Cell(5,6,':',0,0);
        $pdf->MultiCell(0,6,$pengajuan['alasan_pengembalian'],0,'L');

        $pdf->Ln(30);

        $pageWidth = $pdf->GetPageWidth();
        $margin = 25;
        $centerX = $pageWidth / 2;

        $pdf->SetFont('Arial','',11);
        $pdf->SetXY($margin, $pdf->GetY());
        $pdf->Cell(0,6,'Yang Mengajukan',0,0,'L');

        $pdf->SetXY($centerX + 10, $pdf->GetY());
        $pdf->Cell(0,6,'Disetujui Oleh',0,1,'L');

        $pdf->Ln(30);

        $ySign = $pdf->GetY();
        $pdf->SetXY($margin, $ySign);
        $pdf->SetFont('Arial','U',11);
        $pdf->Cell(60,6,'nama',0,2,'C');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(60,6,'NIP.',0,0,'C');

        $pdf->SetXY($centerX + 10, $ySign);
        $pdf->SetFont('Arial','U',11);
        $pdf->Cell(60,6,'nama',0,2,'C');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(60,6,'NIP.',0,0,'C');
        
        $pdf->Output('I', 'surat-pengembalian-'.$id.'.pdf');
        exit;
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
    }

}
