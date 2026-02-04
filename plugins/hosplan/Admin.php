<?php

namespace Plugins\hosplan;

use Systems\AdminModule;

class Admin extends AdminModule
{

    protected function _getUserUnitId()
    {
      $role = $this->core->getUserInfo('role');
      if ($role === 'admin') {
        return null;
      }
      $username = $this->core->getUserInfo('username');
      $fullname = $this->core->getUserInfo('fullname', null, true);
      $unit = $this->db('hosplan_unit')->where('nip', $username)->oneArray();
      if (!$unit && $fullname) {
        $unit = $this->db('hosplan_unit')->where('nama_pegawai', $fullname)->oneArray();
      }
      if (!$unit && $role) {
        $unit = $this->db('hosplan_unit')->where('nama_unit', $role)->oneArray();
      }
      return $unit ? (int)$unit['id_unit'] : null;
    }

    public function navigation()
    {
        return [
            'Kelola' => 'manage',
            'Usulan RKA' => 'usulanrka',
            'Usulan RBA' => 'usulanrba',
            'Surat Usulan' => 'suratusulan',
            'Usulan Buku Belanja' => 'usulanbukbel',
            'Upload Usulan' => 'uploadusulan',
            'Konfirmasi Usulan' => 'konfirmasiusulan',
            'RBA' => 'rba',
            'Akun Belanja' => 'akunbelanja',
        ];
    }

    public function getManage()
    {
      $this->_addHeaderFiles();

      if ($this->core->getUserInfo('role') == 'admin' || $this->core->getUserInfo('username') == '198201092007011006' || $this->core->getUserInfo('username') == '07012022022213011' || $this->core->getUserInfo('username') == '070120420250413003' || $this->core->getUserInfo('username') == '198811092010012003') {
      $sub_modules = [
        ['name' => 'Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'usulan']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RKA'],
        ['name' => 'Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'usulanrba']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RBA'],
        ['name' => 'Surat-Surat', 'url' => url([ADMIN, 'hosplan', 'surat']), 'icon' => 'envelope', 'desc' => 'Data Surat Usulan'],
        ['name' => 'Usulan Buku Belanja', 'url' => url([ADMIN, 'hosplan', 'bukbel']), 'icon' => 'book', 'desc' => 'Data Buku Belanja'],
        ['name' => 'Upload Usulan', 'url' => url([ADMIN, 'hosplan', 'upload']), 'icon' => 'upload', 'desc' => 'Upload Usulan'],
        ['name' => 'Konfirmasi Usulan', 'url' => url([ADMIN, 'hosplan', 'konfirmasi']), 'icon' => 'thumbs-up', 'desc' => 'Konfirmasi Usulan'],
        ['name' => 'Arsip Usulan Disetujui', 'url' => url([ADMIN, 'hosplan', 'arsipsetuju']), 'icon' => 'check', 'desc' => 'Arsip Usulan Disetujui'],
        ['name' => 'Arsip Usulan Ditolak', 'url' => url([ADMIN, 'hosplan', 'arsiptolak']), 'icon' => 'remove', 'desc' => 'Arsip Usulan Ditolak'],
        ['name' => 'Data Master', 'url' => url([ADMIN, 'hosplan', 'master']), 'icon' => 'folder', 'desc' => 'Data Master'],
      ];
    } else {
      $sub_modules = [
        ['name' => 'Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'usulan']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RKA'],
        ['name' => 'Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'usulanrba']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RBA'],
        ['name' => 'Surat Usulan', 'url' => url([ADMIN, 'hosplan', 'suratusulan']), 'icon' => 'envelope', 'desc' => 'Data Surat Usulan'],
        ['name' => 'Usulan Buku Belanja', 'url' => url([ADMIN, 'hosplan', 'bukbel']), 'icon' => 'book', 'desc' => 'Data Buku Belanja'],
        ['name' => 'Upload Usulan', 'url' => url([ADMIN, 'hosplan', 'upload']), 'icon' => 'upload', 'desc' => 'Upload Usulan'],
        ['name' => 'Arsip Usulan Disetujui', 'url' => url([ADMIN, 'hosplan', 'arsipsetuju']), 'icon' => 'check', 'desc' => 'Arsip Usulan Disetujui'],
        ['name' => 'Arsip Usulan Ditolak', 'url' => url([ADMIN, 'hosplan', 'arsiptolak']), 'icon' => 'remove', 'desc' => 'Arsip Usulan Ditolak'],
      ];
    }

      $tabel_rba = $this->db('hosplan_rba') 
            ->select('kode_akun, uraian_kegiatan, anggaran, perubahan, pergeseran, realisasi')
            ->toArray();

      $tabel_akunbelanja = $this->db('hosplan_kodebelanja') 
            ->select('kode_kategori_baru, uraian_kategori, akun_belanja, uraian_akun, kelompok, kode_kelompok, len')
            ->toArray();

      // Hitung jumlah usulan RKA gabungan: harian + tahunan
      $tabel_usulanrka = $this->db('hosplan_usulanrka')->toArray();
      $count_rka = is_array($tabel_usulanrka) ? count($tabel_usulanrka) : 0;
      $tabel_usulanrka_tahunan = $this->db('hosplan_usulanrka_tahunan')->toArray();
      $count_tahunan = is_array($tabel_usulanrka_tahunan) ? count($tabel_usulanrka_tahunan) : 0;
      $jumlah_usulanrka = $count_rka + $count_tahunan;

      $tabel_usulanrba = $this->db('hosplan_usulanrba')->toArray();
      $jumlah_usulanrba = is_array($tabel_usulanrba) ? count($tabel_usulanrba) : 0;

      $tabel_suratusulan = $this->db('hosplan_surat')->toArray();
      $jumlah_suratusulan = is_array($tabel_suratusulan) ? count($tabel_suratusulan) : 0;

      $tabel_usulanbukbel = $this->db('hosplan_bukbel')->toArray();
      $jumlah_usulanbukbel = is_array($tabel_usulanbukbel) ? count($tabel_usulanbukbel) : 0;

      $pendingRKA = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();

      $pendingRBA = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();

      $pendingBukbel = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();

      $totalPending = (is_array($pendingRKA) ? count($pendingRKA) : 0)
        + (is_array($pendingRBA) ? count($pendingRBA) : 0)
        + (is_array($pendingBukbel) ? count($pendingBukbel) : 0);

      return $this->draw('manage.html', [
        'sub_modules' => $sub_modules,
        'tabel_rba' => $tabel_rba,
        'tabel_akunbelanja' => $tabel_akunbelanja,
        'jumlah_usulanrka' => $jumlah_usulanrka,
        'jumlah_usulanrba' => $jumlah_usulanrba,
        'jumlah_suratusulan' => $jumlah_suratusulan,
        'jumlah_usulanbukbel' => $jumlah_usulanbukbel,
        'totalPending' => $totalPending,
      ]);
    }

    public function getSurat()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
      ['name' => 'Surat Usulan', 'url' => url([ADMIN, 'hosplan', 'suratusulan']), 'icon' => 'envelope', 'desc' => 'Data Surat Usulan'],
      ['name' => 'Surat Telaahan', 'url' => url([ADMIN, 'hosplan', 'surattelaahan']), 'icon' => 'envelope', 'desc' => 'Data Surat Telaahan'],
      ];

      return $this->draw('surat.html', ['sub_modules' => $sub_modules]);
    }

    // <--- Surat Usulan -->

    public function getSuratUsulan()
    {
      $this->_addHeaderFiles();

      $unit = $this->db('hosplan_unit')->toArray();

      $userUnitId = $this->_getUserUnitId();

      $query = $this->db('hosplan_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_surat.id_unit');

      if ($userUnitId) {
        $query->where('hosplan_surat.id_unit', $userUnitId);
      }

      $tabel_suratusulan = $query->toArray();

      if (is_array($tabel_suratusulan)) {
              foreach ($tabel_suratusulan as &$row) {
                  $row['json_data'] = base64_encode(json_encode($row));
              }
          }

      $totalUploadSuratUsulan = is_array($tabel_suratusulan) ? count($tabel_suratusulan) : 0;
      $isUnitSelected = ($userUnitId && $userUnitId > 0);

      return $this->draw('suratusulan.html', [
        'tabel_suratusulan' => $tabel_suratusulan,
        'unit' => $unit,
        'totalUploadSuratUsulan' => $totalUploadSuratUsulan,
        'isUnitSelected' => $isUnitSelected,
        'selectedUnit' => $userUnitId ?: 'all',
      ]);
    }

    public function getTambahSuratUsulan()
    {
      $unit = $this->db('hosplan_unit')->toArray();

      return $this->draw('tambahsuratusulan.html', [
        'unit' => $unit,
        'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postSaveSuratUsulan()
    {
      $userUnitId = $this->_getUserUnitId();
      $role = $this->core->getUserInfo('role');
      $idUnitToSave = ($role === 'admin') ? $_POST['id_unit'] : ($userUnitId ?: $_POST['id_unit']);
      $this->db('hosplan_surat')->save([
        'no_surat' => $_POST['no_surat'],
        'tgl_surat' => $_POST['tgl_surat'],
        'lampiran' => $_POST['lampiran'],
        'perihal' => $_POST['perihal'],
        'id_unit' => $idUnitToSave,
      ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'suratusulan'], ['unit' => $unit]));
    }

    public function getEditSuratUsulan($id_surat)
    {
      $this->_addHeaderFiles();

      $unit = $this->db('hosplan_unit')
            ->toArray();
    
      $suratusulan = $this->db('hosplan_surat')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_surat.id_unit')
          ->where('hosplan_surat.id_surat', $id_surat)
          ->toArray();
    
      if (!$suratusulan) {
          return $this->draw('error.html', ['message' => 'Surat Usulan tidak ditemukan']);
      }
    
      return $this->draw('editsuratusulan.html', [
          'suratusulan' => $suratusulan[0],
          'unit'  => $unit,
      ]);
    }

    public function postUpdateSuratUsulan()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_surat'])) {
          redirect(url([ADMIN, 'hosplan', 'suratusulan']));  
          return;
      }

      $id_surat = $_POST['id_surat'];

      $cek = $this->db('hosplan_surat')->where('id_surat', $id_surat)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'suratusulan']));
          return;
      }

      $this->db('hosplan_surat')
          ->where('id_surat', $id_surat)
          ->update([
              'id_surat' => $id_surat,
              'no_surat' => $_POST['no_surat'],
              'tgl_surat' => $_POST['tgl_surat'],
              'lampiran' => $_POST['lampiran'],
              'perihal' => $_POST['perihal'],
              // Non-admin tidak boleh mengubah ke unit lain
              'id_unit' => ($this->core->getUserInfo('role') === 'admin') ? $_POST['id_unit'] : ($this->_getUserUnitId() ?: $_POST['id_unit']),
          ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'suratusulan'], ['unit' => $unit, 'edited' => 1]));
    }

    public function getDeleteSuratUsulan($id_surat)
    {
      $cek = $this->db('hosplan_surat')->where('id_surat', $id_surat)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'suratusulan']));
          return;
      }
      // Hapus data terkait di tabel upload RKA yang mereferensikan surat ini
      $relatedRka = $this->db('hosplan_uploadrka')->where('id_surat', $id_surat)->toArray();
      if (!empty($relatedRka)) {
        $this->db('hosplan_uploadrka')->where('id_surat', $id_surat)->delete();
      }
      // Lanjut hapus surat
      $this->db('hosplan_surat')->where('id_surat', $id_surat)->delete();
  
      $unit = isset($cek['id_unit']) ? $cek['id_unit'] : (isset($_GET['unit']) ? $_GET['unit'] : '');
      redirect(url([ADMIN, 'hosplan', 'suratusulan'], ['unit' => $unit, 'deleted' => 1]));  
    }

    public function getCetakSuratUsulan($id_surat)
    {
      // $this->_addHeaderFiles();
    
      $unit = $this->db('hosplan_unit')->toArray();
    
      $suratusulan = $this->db('hosplan_surat')
          ->join('hosplan_unit', 'hosplan_unit.id_unit = hosplan_surat.id_unit', 'LEFT')
          ->where('hosplan_surat.id_surat', $id_surat)
          ->toArray();
    
      if (!$suratusulan) {
          return $this->draw('error.html', ['message' => 'Surat tidak ditemukan']);
      }

      $ttd_files = [];
      $ttd_path = __DIR__ . '/img/ttd/';
      if (is_dir($ttd_path)) {
          $files = scandir($ttd_path);
          foreach ($files as $file) {
              if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
              }
          }
      }
    
      echo $this->draw('cetaksuratusulan.html', [
          'suratusulan' => $suratusulan[0],
          'unit'  => $unit,
          'ttd_files' => $ttd_files
      ]);
      exit();
    }

    public function getCetakSuratTelaahan($id_surat)
    {
      $unit = $this->db('hosplan_unit')->toArray();

      $surattelaahan = $this->db('hosplan_telaahan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit = hosplan_telaahan.id_unit', 'LEFT')
          ->where('hosplan_telaahan.id_surat', $id_surat)
          ->toArray();

      if (!$surattelaahan) {
          return $this->draw('error.html', ['message' => 'Surat Telaahan tidak ditemukan']);
      }

      $ttd_files = [];
      $ttd_path = __DIR__ . '/img/ttd/';
      if (is_dir($ttd_path)) {
          $files = scandir($ttd_path);
          foreach ($files as $file) {
              if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
              }
          }
      }

      echo $this->draw('cetaksurattelaahan.html', [
          'surattelaahan' => $surattelaahan[0],
          'unit'  => $unit,
          'ttd_files' => $ttd_files
      ]);
      exit();
    }


    public function getSuratTelaahan()
    {
      $this->_addHeaderFiles();

      $unit = $this->db('hosplan_unit')->toArray();

      $userUnitId = $this->_getUserUnitId();

      $query = $this->db('hosplan_telaahan')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_telaahan.id_unit');

      if ($userUnitId) {
        $query->where('hosplan_telaahan.id_unit', $userUnitId);
      }

      $tabel_surattelaahan = $query->toArray();
      
      // Encode data for frontend usage
          if (is_array($tabel_surattelaahan)) {
              foreach ($tabel_surattelaahan as &$row) {
                  $row['json_data'] = base64_encode(json_encode($row));
              }
          }

      $totalUploadSuratTelaahan = is_array($tabel_surattelaahan) ? count($tabel_surattelaahan) : 0;
      $isUnitSelected = ($userUnitId && $userUnitId > 0);

      return $this->draw('surattelaahan.html', [
        'tabel_surattelaahan' => $tabel_surattelaahan,
        'unit' => $unit,
        'totalUploadSuratTelaahan' => $totalUploadSuratTelaahan,
        'isUnitSelected' => $isUnitSelected,
        'selectedUnit' => $userUnitId ?: 'all',
      ]);
    }

    public function getTambahSuratTelaahan()
    {
      $unit = $this->db('hosplan_unit')->toArray();

      return $this->draw('tambahsurattelaahan.html', [
        'unit' => $unit,
        'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postSaveSuratTelaahan()
    {
      $userUnitId = $this->_getUserUnitId();
      $role = $this->core->getUserInfo('role');
      $idUnitToSave = ($role === 'admin') ? $_POST['id_unit'] : ($userUnitId ?: $_POST['id_unit']);
      $this->db('hosplan_telaahan')->save([
        'no_surat' => $_POST['no_surat'],
        'tgl_surat' => $_POST['tgl_surat'],
        'perihal' => $_POST['perihal'],
        'permasalahan' => $_POST['permasalahan'],
        'pra_anggapan' => $_POST['pra_anggapan'],
        'fakta' => $_POST['fakta'],
        'pembahasan' => $_POST['pembahasan'],
        'saran_tindakan' => $_POST['saran_tindakan'],
        'penutup' => $_POST['penutup'],
        'id_unit' => $idUnitToSave,
      ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'surattelaahan'], ['unit' => $unit]));
    }

    public function getEditSuratTelaahan($id_surat)
    {
      $this->_addHeaderFiles();

      $unit = $this->db('hosplan_unit')
            ->toArray();
    
      $surattelaahan = $this->db('hosplan_telaahan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_telaahan.id_unit')
          ->where('hosplan_telaahan.id_surat', $id_surat)
          ->toArray();
    
      if (!$surattelaahan) {
          return $this->draw('error.html', ['message' => 'Surat Telaahan tidak ditemukan']);
      }
    
      return $this->draw('editsurattelaahan.html', [
          'surattelaahan' => $surattelaahan[0],
          'unit'  => $unit,
      ]);
    }

    public function postUpdateSuratTelaahan()
    {
      if (!isset($_POST['id_surat'])) {
          redirect(url([ADMIN, 'hosplan', 'surattelaahan']));
          return;
      }

      $id_surat = $_POST['id_surat'];
      $cek = $this->db('hosplan_telaahan')->where('id_surat', $id_surat)->oneArray();

      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'surattelaahan']));
          return;
      }

      // $userUnitId = $this->_getUserUnitId();
      // $role = $this->core->getUserInfo('role');
      // $idUnitToSave = ($role === 'admin') ? $_POST['id_unit'] : ($userUnitId ?: $_POST['id_unit']);

      $this->db('hosplan_telaahan')
        ->where('id_surat', $id_surat)
        ->update([
            'no_surat' => $_POST['no_surat'],
            'tgl_surat' => $_POST['tgl_surat'],
            'perihal' => $_POST['perihal'],
            'permasalahan' => $_POST['permasalahan'],
            'pra_anggapan' => $_POST['pra_anggapan'],
            'fakta' => $_POST['fakta'],
            'pembahasan' => $_POST['pembahasan'],
            'saran_tindakan' => $_POST['saran_tindakan'],
            // Non-admin tidak boleh mengubah ke unit lain
            'id_unit' => ($this->core->getUserInfo('role') === 'admin') ? $_POST['id_unit'] : ($this->_getUserUnitId() ?: $_POST['id_unit']),
        ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'surattelaahan'], ['unit' => $unit, 'edited' => 1]));
    }

    public function getDeleteSuratTelaahan($id_surat)
    {
      $cek = $this->db('hosplan_telaahan')->where('id_surat', $id_surat)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'surattelaahan']));
          return;
      }

      $this->db('hosplan_telaahan')->where('id_surat', $id_surat)->delete();
  
      $unit = isset($cek['id_unit']) ? $cek['id_unit'] : (isset($_GET['unit']) ? $_GET['unit'] : '');
      redirect(url([ADMIN, 'hosplan', 'surattelaahan'], ['unit' => $unit, 'deleted' => 1]));  
    }

    // <-- Usulan RKA -->

    public function getUsulan()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'Usulan RKA Harian', 'url' => url([ADMIN, 'hosplan', 'usulanrka']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RKA Harian'],
        ['name' => 'Usulan RKA Tahunan', 'url' => url([ADMIN, 'hosplan', 'usulanrkatahunan']), 'icon' => 'clipboard', 'desc' => 'Data Usulan RKA Tahunan'],
      ];

      // $today = date('Y-m-d');
      // $currentYear = date('Y');

      $tabel_usulanrka = $this->db('hosplan_usulanrka')
        ->toArray();
      $jumlahRKAHarian = is_array($tabel_usulanrka) ? count($tabel_usulanrka) : 0;
      $tahunanRows = $this->db('hosplan_usulanrka_tahunan')
        ->toArray();
      $jumlahRKATahunan = is_array($tahunanRows) ? count($tahunanRows) : 0;

      return $this->draw('usulan.html', [
        'sub_modules' => $sub_modules,
        'jumlahRKAHarian' => $jumlahRKAHarian,
        'jumlahRKATahunan' => $jumlahRKATahunan,
      ]);
    }

    public function getUsulanRKA()
    {
      $this->_addHeaderFiles();

      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      $userUnitId = $this->_getUserUnitId();

      $query = $this->db('hosplan_usulanrka')
        ->leftJoin('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
        ->leftJoin('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka.id_unit');

      if ($userUnitId) {
        $query->where('hosplan_usulanrka.id_unit', $userUnitId);
      }

      $tabel_usulanrka = $query->toArray();
      $totalUploadRKA = is_array($tabel_usulanrka) ? count($tabel_usulanrka) : 0;
      $isUnitSelected = ($userUnitId && $userUnitId > 0);

      return $this->draw('usulanrka.html', [
        'tabel_usulanrka' => $tabel_usulanrka,
        'kode' => $kode,
        'satuan' => $satuan,
        'unit' => $unit,
        'totalUploadRKA' => $totalUploadRKA,
        'isUnitSelected' => $isUnitSelected,
        'selectedUnit' => $userUnitId ?: 'all',
      ]);
    }

    public function getTambahUsulanRKA()
    {
      // $this->_addHeaderFiles();

      $kode = $this->db('hosplan_kodebelanja')
            ->toArray();

      $satuan = $this->db('hosplan_satuan')
            ->toArray();

      $unit = $this->db('hosplan_unit')
            ->toArray();

      return $this->draw('tambahusulanrka.html', [
        'kode' => $kode,
        'satuan' => $satuan,
        'unit' => $unit,
        'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postSaveUsulanRKA()
    {
      $userUnitId = $this->_getUserUnitId();
      $role = $this->core->getUserInfo('role');
      $idUnitToSave = ($role === 'admin') ? $_POST['id_unit'] : ($userUnitId ?: $_POST['id_unit']);
      $this->db('hosplan_usulanrka')->save([
        'id_kode' => $_POST['id_kode'],
        'nama_komponen' => $_POST['nama_komponen'],
        'spesifikasi' => $_POST['spesifikasi'],
        'merek' => $_POST['merek'],
        'tgl_usulan' => $_POST['tgl_usulan'],
        'volume' => $_POST['volume'],
        'harga' => $_POST['harga'],
        'id_satuan' => $_POST['id_satuan'],
        'jumlah' => $_POST['jumlah'],
        'keterangan' => $_POST['keterangan'],
        'id_unit' => $idUnitToSave,
      ]);

      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'usulanrka'], ['kode' => $kode, 'satuan' => $satuan, 'unit' => $unit, 'added' => 1]));
    }

    public function getEditUsulanRKA($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $kode = $this->db('hosplan_kodebelanja')
            ->toArray();

      $satuan = $this->db('hosplan_satuan')
            ->toArray();

      $unit = $this->db('hosplan_unit')
            ->toArray();
    
      $usulanrka = $this->db('hosplan_usulanrka')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka.id_unit')
          ->where('hosplan_usulanrka.id_usulan', $id_usulan)
          ->toArray();
    
      if (!$usulanrka) {
          return $this->draw('error.html', ['message' => 'Usulan RKA tidak ditemukan']);
      }
    
      return $this->draw('editusulanrka.html', [
          'usulanrka' => $usulanrka[0],
          'unit'  => $unit,
          'kode' => $kode,
          'satuan' => $satuan,
          'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postUpdateUsulanRKA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'usulanrka']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_usulanrka')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrka']));
          return;
      }

      $this->db('hosplan_usulanrka')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_kode' => $_POST['id_kode'],
              'nama_komponen' => $_POST['nama_komponen'],
              'spesifikasi' => $_POST['spesifikasi'],
              'merek' => $_POST['merek'],
              'tgl_usulan' => $_POST['tgl_usulan'],
              'volume' => $_POST['volume'],
              'harga' => $_POST['harga'],
              'id_satuan' => $_POST['id_satuan'],
              'jumlah' => $_POST['jumlah'],
              'keterangan' => $_POST['keterangan'],
              'id_unit' => $_POST['id_unit']
          ]);

      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanrka'], ['kode' => $kode, 'satuan' => $satuan, 'unit' => $unit, 'edited' => 1]));
    }

    public function getDeleteUsulanRKA($id_usulan)
    {
      $cek = $this->db('hosplan_usulanrka')->where('id_usulan', $id_usulan)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrka']));
          return;
      }
      $this->db('hosplan_usulanrka')->where('id_usulan', $id_usulan)->delete();
    
      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';

      redirect(url([ADMIN, 'hosplan', 'usulanrka'], ['kode' => $kode, 'satuan' => $satuan, 'unit' => $unit, 'deleted' => 1]));  
    }

    public function postCetakUsulanRKA($id_usulan = null)
    {
      // Siapkan referensi yang mungkin dipakai di view
      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Jika form mengirimkan beberapa ID via checkbox
      if (isset($_POST['id_usulan'])) {
        $ids = $_POST['id_usulan'];
        if (!is_array($ids)) { $ids = [$ids]; }
        // Amankan ke integer
        $ids = array_map('intval', $ids);

        // Bangun filter dengan grup OR agar kompatibel dengan QueryWrapper
        $q = $this->db('hosplan_usulanrka')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka.id_unit');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_usulanrka.id_usulan', (int)$id);
          }
        });

        // Eksekusi (hasil tidak dipakai langsung karena kita redirect)
        $rows = $q->toArray();

        // Simpan payload ke session dan redirect ke GET agar aman (hindari resubmission)
        redirect(url([ADMIN, 'hosplan', 'cetakusulanrka']), $_POST);
      }

      // Fallback: jika dipanggil dengan satu ID di route
      if ($id_usulan !== null) {
        $usulanrka = $this->db('hosplan_usulanrka')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_usulanrka.id_unit')
          ->where('hosplan_usulanrka.id_usulan', '=', (int)$id_usulan)
          ->toArray();

        return $this->draw('cetakusulanrka.html', [
          'usulanrka' => $usulanrka,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'userUnitId' => $this->_getUserUnitId(),
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      // Jika tidak ada data disediakan, kembali ke daftar usulan
      redirect(url([ADMIN, 'hosplan', 'usulanrka']));
    }

    // Tambahkan handler GET agar akses via URL (mis. dengan token ?t=...) tidak kosong
    public function getCetakUsulanRKA($id_usulan = null)
    {
      // Siapkan referensi untuk view
      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Ambil data dari POST atau dari redirect session bila ada
      $payload = $_POST;
      if (empty($payload)) {
        $redirectData = getRedirectData();
        if (!empty($redirectData) && is_array($redirectData)) {
          $payload = $redirectData;
        }
      }

      // Alternatif: dukungan GET ?ids=31,35
      if (empty($payload['id_usulan']) && isset($_GET['ids'])) {
        $raw = $_GET['ids'];
        $list = preg_split('/[\s,]+/', $raw);
        $payload['id_usulan'] = array_filter(array_map('intval', $list));
      }

      if (isset($payload['id_usulan'])) {
        $ids = $payload['id_usulan'];
        if (!is_array($ids)) { $ids = [$ids]; }
        $ids = array_map('intval', $ids);

        $q = $this->db('hosplan_usulanrka')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka.id_unit');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_usulanrka.id_usulan', (int)$id);
          }
        });

        $rows = $q->toArray();

        return $this->draw('cetakusulanrka.html', [
          'usulanrka' => $rows,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'userUnitId' => $this->_getUserUnitId(),
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      if ($id_usulan !== null) {
        $usulanrka = $this->db('hosplan_usulanrka')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka.id_satuan')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_usulanrka.id_unit')
          ->where('hosplan_usulanrka.id_usulan', '=', (int)$id_usulan)
          ->toArray();

        return $this->draw('cetakusulanrka.html', [
          'usulanrka' => $usulanrka,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'userUnitId' => $this->_getUserUnitId(),
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      redirect(url([ADMIN, 'hosplan', 'usulanrka']));
    }

    public function getUsulanRKATahunan()
    {
      $this->_addHeaderFiles();

      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      $userUnitId = $this->_getUserUnitId();

      $query = $this->db('hosplan_usulanrka_tahunan')
        ->leftJoin('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
        ->leftJoin('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka_tahunan.id_unit');

      if ($userUnitId) {
        $query->where('hosplan_usulanrka_tahunan.id_unit', $userUnitId);
      }

      $tabel_usulanrkatahunan = $query->toArray();
      $totalUploadRKATahunan = is_array($tabel_usulanrkatahunan) ? count($tabel_usulanrkatahunan) : 0;
      $isUnitSelected = ($userUnitId && $userUnitId > 0);

      return $this->draw('usulanrkatahunan.html', [
        'tabel_usulanrkatahunan' => $tabel_usulanrkatahunan,
        'kode' => $kode,
        'satuan' => $satuan,
        'unit' => $unit,
        'totalUploadRKATahunan' => $totalUploadRKATahunan,
        'isUnitSelected' => $isUnitSelected,
        'selectedUnit' => $userUnitId ?: 'all',
      ]);
    }

    public function getTambahUsulanRKATahunan()
    {
      // $this->_addHeaderFiles();

      $kode = $this->db('hosplan_kodebelanja')
            ->toArray();

      $satuan = $this->db('hosplan_satuan')
            ->toArray();

      $unit = $this->db('hosplan_unit')
            ->toArray();

      return $this->draw('tambahusulanrkatahunan.html', [
        'kode' => $kode,
        'satuan' => $satuan,
        'unit' => $unit,
        'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postSaveUsulanRKATahunan()
    {
      $this->db('hosplan_usulanrka_tahunan')->save([
        'id_kode' => $_POST['id_kode'],
        'nama_komponen' => $_POST['nama_komponen'],
        'spesifikasi' => $_POST['spesifikasi'],
        'merek' => $_POST['merek'],
        'tahun' => $_POST['tahun'],
        'volume' => $_POST['volume'],
        'harga' => $_POST['harga'],
        'id_satuan' => $_POST['id_satuan'],
        'jumlah' => $_POST['jumlah'],
        'keterangan' => $_POST['keterangan'],
        'id_unit' => $_POST['id_unit'],
      ]);

      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $this->notify('success', 'Data berhasil ditambahkan');
      redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan'], ['kode' => $kode, 'satuan' => $satuan]));
    }

    public function getEditUsulanRKATahunan($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $kode = $this->db('hosplan_kodebelanja')
            ->toArray();

      $satuan = $this->db('hosplan_satuan')
            ->toArray();

      $unit = $this->db('hosplan_unit')
            ->toArray();
    
      $usulanrkatahunan = $this->db('hosplan_usulanrka_tahunan')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka_tahunan.id_unit')
          ->where('hosplan_usulanrka_tahunan.id_usulan', $id_usulan)
          ->toArray();
    
      if (!$usulanrkatahunan) {
          return $this->draw('error.html', ['message' => 'Usulan RKA tidak ditemukan']);
      }
    
      return $this->draw('editusulanrkatahunan.html', [
          'usulanrkatahunan' => $usulanrkatahunan[0],
          'unit'  => $unit,
          'kode' => $kode,
          'satuan' => $satuan,
          'userUnitId' => $this->_getUserUnitId()
      ]);
    }

    public function postUpdateUsulanRKATahunan()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_usulanrka_tahunan')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan']));
          return;
      }

      $this->db('hosplan_usulanrka_tahunan')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_kode' => $_POST['id_kode'],
              'nama_komponen' => $_POST['nama_komponen'],
              'spesifikasi' => $_POST['spesifikasi'],
              'merek' => $_POST['merek'],
              'tahun' => $_POST['tahun'],
              'volume' => $_POST['volume'],
              'harga' => $_POST['harga'],
              'id_satuan' => $_POST['id_satuan'],
              'jumlah' => $_POST['jumlah'],
              'keterangan' => $_POST['keterangan'],
              'id_unit' => $_POST['id_unit']
          ]);

      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan'], ['kode' => $kode, 'satuan' => $satuan, 'unit' => $unit, 'edited' => 1]));
    }

    public function getDeleteUsulanRKATahunan($id_usulan)
    {
      $cek = $this->db('hosplan_usulanrka_tahunan')->where('id_usulan', $id_usulan)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan']));
          return;
      }
      $this->db('hosplan_usulanrka_tahunan')->where('id_usulan', $id_usulan)->delete();
    
      $kode = isset($_GET['kode']) ? $_GET['kode'] : '';
      $satuan = isset($_GET['satuan']) ? $_GET['satuan'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';

      redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan'], ['kode' => $kode, 'satuan' => $satuan, 'unit' => $unit, 'deleted' => 1]));  
    }

    public function postCetakUsulanRKATahunan($id_usulan = null)
    {
      // Siapkan referensi yang mungkin dipakai di view
      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Jika form mengirimkan beberapa ID via checkbox
      if (isset($_POST['id_usulan'])) {
        $ids = $_POST['id_usulan'];
        if (!is_array($ids)) { $ids = [$ids]; }
        // Amankan ke integer
        $ids = array_map('intval', $ids);

        // Bangun filter dengan grup OR agar kompatibel dengan QueryWrapper
        $q = $this->db('hosplan_usulanrka_tahunan')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka_tahunan.id_unit');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_usulanrka_tahunan.id_usulan', (int)$id);
          }
        });

        // Eksekusi (hasil tidak dipakai langsung karena kita redirect)
        $rows = $q->toArray();

        // Simpan payload ke session dan redirect ke GET agar aman (hindari resubmission)
        redirect(url([ADMIN, 'hosplan', 'cetakusulanrkatahunan']), $_POST);
      }

      // Fallback: jika dipanggil dengan satu ID di route
      if ($id_usulan !== null) {
        $usulanrkatahunan = $this->db('hosplan_usulanrka_tahunan')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_usulanrka_tahunan.id_unit')
          ->where('hosplan_usulanrka_tahunan.id_usulan', '=', (int)$id_usulan)
          ->toArray();

        return $this->draw('cetakusulanrkatahunan.html', [
          'usulanrkatahunan' => $usulanrkatahunan,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      // Jika tidak ada data disediakan, kembali ke daftar usulan
      redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan']));
    }

    // Tambahkan handler GET agar akses via URL (mis. dengan token ?t=...) tidak kosong
    public function getCetakUsulanRKATahunan($id_usulan = null)
    {
      // Siapkan referensi untuk view
      $kode = $this->db('hosplan_kodebelanja')->toArray();
      $satuan = $this->db('hosplan_satuan')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Ambil data dari POST atau dari redirect session bila ada
      $payload = $_POST;
      if (empty($payload)) {
        $redirectData = getRedirectData();
        if (!empty($redirectData) && is_array($redirectData)) {
          $payload = $redirectData;
        }
      }

      // Alternatif: dukungan GET ?ids=31,35
      if (empty($payload['id_usulan']) && isset($_GET['ids'])) {
        $raw = $_GET['ids'];
        $list = preg_split('/[\s,]+/', $raw);
        $payload['id_usulan'] = array_filter(array_map('intval', $list));
      }

      if (isset($payload['id_usulan'])) {
        $ids = $payload['id_usulan'];
        if (!is_array($ids)) { $ids = [$ids]; }
        $ids = array_map('intval', $ids);

        $q = $this->db('hosplan_usulanrka_tahunan')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
          ->join('hosplan_unit', 'hosplan_unit.id_unit=hosplan_usulanrka_tahunan.id_unit');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_usulanrka_tahunan.id_usulan', (int)$id);
          }
        });

        $rows = $q->toArray();

        return $this->draw('cetakusulanrkatahunan.html', [
          'usulanrkatahunan' => $rows,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      if ($id_usulan !== null) {
        $usulanrkatahunan = $this->db('hosplan_usulanrka_tahunan')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrka_tahunan.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrka_tahunan.id_satuan')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_usulanrka_tahunan.id_unit')
          ->where('hosplan_usulanrka_tahunan.id_usulan', '=', (int)$id_usulan)
          ->toArray();

        return $this->draw('cetakusulanrkatahunan.html', [
          'usulanrkatahunan' => $usulanrkatahunan,
          'kode'  => $kode,
          'satuan'  => $satuan,
          'unit'  => $unit,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
      }

      redirect(url([ADMIN, 'hosplan', 'usulanrkatahunan']));
    }

// <-- Usulan RBA -->

    public function getUsulanRBA()
    {
      $this->_addHeaderFiles();

      $rba = $this->db('hosplan_rba')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter per unit: jika belum dipilih, jangan tampilkan data
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_usulanrba')
        ->leftJoin('hosplan_rba', 'hosplan_rba.id_rba=hosplan_usulanrba.id_rba')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_usulanrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_usulanrba.id_pptk', $selectedPptk);
      }

      $tabel_usulanrba = $query->toArray();
      $totalUsulanRBA = is_array($tabel_usulanrba) ? count($tabel_usulanrba) : 0;
      $isPptkSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('usulanrba.html', [
        'tabel_usulanrba' => $tabel_usulanrba,
        'rba' => $rba,
        'pptk' => $pptk,
        'totalUsulanRBA' => $totalUsulanRBA,
        'isPptkSelected' => $isPptkSelected,
        'selectedPptk' => $selectedPptk,
      ]);
    }

    public function getTambahUsulanRBA()
    {
      // $this->_addHeaderFiles();

      $rba = $this->db('hosplan_rba')
            ->toArray();

      $pptk = $this->db('hosplan_pptk')
            ->toArray();

      return $this->draw('tambahusulanrba.html', ['rba' => $rba, 'pptk' => $pptk]);
    }

    public function postSaveUsulanRBA()
    {
      $this->db('hosplan_usulanrba')->save([
        'id_usulanrba' => $_POST['id_usulanrba'],
        'id_rba' => $_POST['id_rba'],
        'id_pptk' => $_POST['id_pptk'],
        'permintaan_anggaran' => $_POST['permintaan_anggaran'],
        'total' => $_POST['total'],
      ]);

      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanrba'], ['rba' => $rba, 'pptk' => $pptk, 'added' => 1]));
    }

    public function getEditUsulanRBA($id_usulanrba)
    {
      $this->_addHeaderFiles();
      
      $rba = $this->db('hosplan_rba')
            ->toArray();

      $pptk = $this->db('hosplan_pptk')
            ->toArray();

      $usulanrba = $this->db('hosplan_usulanrba') 
            ->join('hosplan_rba', 'hosplan_rba.id_rba=hosplan_usulanrba.id_rba')
            ->join('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_usulanrba.id_pptk')
            ->where('hosplan_usulanrba.id_usulanrba', $id_usulanrba)
            ->toArray();
    
      if (!$usulanrba) {
          return $this->draw('error.html', ['message' => 'Usulan RBA tidak ditemukan']);
      }
    
      return $this->draw('editusulanrba.html', [
          'usulanrba' => $usulanrba[0],
          'rba'  => $rba,
          'pptk' => $pptk
      ]);
    }

    public function postUpdateUsulanRBA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulanrba'])) {
          redirect(url([ADMIN, 'hosplan', 'usulanrba']));  
          return;
      }

      $id_usulanrba = $_POST['id_usulanrba'];

      $cek = $this->db('hosplan_usulanrba')->where('id_usulanrba', $id_usulanrba)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrka']));
          return;
      }

      $this->db('hosplan_usulanrba')
          ->where('id_usulanrba', $id_usulanrba)
          ->update([
              'id_usulanrba' => $id_usulanrba,
              'id_rba' => $_POST['id_rba'],
              'id_pptk' => $_POST['id_pptk'],
              'permintaan_anggaran' => $_POST['permintaan_anggaran'],
              'total' => $_POST['total'], 
          ]);

      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanrba'], ['rba' => $rba, 'pptk' => $pptk]));
    }

    public function getDeleteUsulanRBA($id_usulanrba)
    {
      $cek = $this->db('hosplan_usulanrba')->where('id_usulanrba', $id_usulanrba)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanrba']));
          return;
      }
      $this->db('hosplan_usulanrba')->where('id_usulanrba', $id_usulanrba)->delete();
    
      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanrba'], ['rba' => $rba, 'pptk' => $pptk]));  
    }

    public function postCetakUsulanRBA($id_usulanrba = null)
    {
      // Siapkan referensi untuk view
      $rba = $this->db('hosplan_rba')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Ambil data dari POST atau dari redirect session bila ada
      $payload = $_POST;
      if (empty($payload)) {
        $redirectData = getRedirectData();
        if (!empty($redirectData) && is_array($redirectData)) {
          $payload = $redirectData;
        }
      }

      // Alternatif: dukungan GET ?ids=31,35
      if (empty($payload['id_usulanrba']) && isset($_GET['ids'])) {
        $raw = $_GET['ids'];
        $list = preg_split('/[\s,]+/', $raw);
        $payload['id_usulanrba'] = array_filter(array_map('intval', $list));
      }

      if (isset($payload['id_usulanrba'])) {
        $ids = $payload['id_usulanrba'];
        if (!is_array($ids)) { $ids = [$ids]; }
        $ids = array_map('intval', $ids);

        $q = $this->db('hosplan_usulanrba')
          ->join('hosplan_rba', 'hosplan_rba.id_rba=hosplan_usulanrba.id_rba')
          ->join('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_usulanrba.id_pptk');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_usulanrba.id_usulanrba', (int)$id);
          }
        });

        $rows = $q->toArray();

        echo $this->draw('cetakusulanrba.html', [
          'usulanrba' => $rows,
          'rba'  => $rba,
          'pptk'  => $pptk,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
        exit();
      }

      if ($id_usulanrba !== null) {
        $usulanrba = $this->db('hosplan_usulanrba')
          ->join('hosplan_kodebelanja', 'hosplan_kodebelanja.id_kode=hosplan_usulanrba.id_kode')
          ->join('hosplan_satuan', 'hosplan_satuan.id_satuan=hosplan_usulanrba.id_satuan')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_usulanrba.id_unit')
          ->where('hosplan_usulanrba.id_usulanrba', '=', (int)$id_usulanrba)
          ->toArray();

        return $this->draw('cetakusulanrka.html', [
          'usulanrba' => $usulanrba,
          'rba'  => $rba,
          'pptk' => $pptk,
        ]);
      }
    }

    public function getBukbel()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'Usulan Buku Belanja', 'url' => url([ADMIN, 'hosplan', 'usulanbukbel']), 'icon' => 'book', 'desc' => 'Data Usulan Buku Belanja'],
        ['name' => 'Realisasi Buku Belanja', 'url' => url([ADMIN, 'hosplan', 'rebel']), 'icon' => 'book', 'desc' => 'Data Realisasi Buku Belanja'],
      ];

      // $today = date('Y-m-d');
      // $currentYear = date('Y');

      $tabel_usulanbukbel = $this->db('hosplan_bukbel')->toArray();
      $jumlah_usulanbukbel = is_array($tabel_usulanbukbel) ? count($tabel_usulanbukbel) : 0;

      return $this->draw('bukbel.html', [
        'sub_modules' => $sub_modules,
        'jumlah_usulanbukbel' => $jumlah_usulanbukbel,
      ]);
    }

    // <--- Usulan Buku Belanja -->

    public function getUsulanBukbel()
    {
      $this->_addHeaderFiles();

      $rba = $this->db('hosplan_rba')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedRBARaw = isset($_GET['id_rba']) ? $_GET['id_rba'] : 'all';
      $selectedRBA = is_numeric($selectedRBARaw) ? (int) $selectedRBARaw : $selectedRBARaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_bukbel')
        ->leftJoin('hosplan_rba', 'hosplan_rba.id_rba=hosplan_bukbel.id_rba');

      if ($selectedRBA !== 'all' && is_int($selectedRBA)) {
        $query->where('hosplan_bukbel.id_rba', $selectedRBA);
      }

      $tabel_usulanbukbel = $query->toArray();
      $totalUsulanBukbel = is_array($tabel_usulanbukbel) ? count($tabel_usulanbukbel) : 0;
      $isUnitSelected = ($selectedRBA !== 'all' && is_int($selectedRBA) && $selectedRBA > 0);

      return $this->draw('usulanbukbel.html', [
        'tabel_usulanbukbel' => $tabel_usulanbukbel,
        'rba' => $rba,
        'selectedRBA' => $selectedRBA,
        'totalUsulanBukbel' => $totalUsulanBukbel,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getTambahUsulanBukbel()
    {
      // $this->_addHeaderFiles();

      $rba = $this->db('hosplan_rba')
            ->toArray();

      return $this->draw('tambahusulanbukbel.html', ['rba' => $rba]);
    }

    public function postSaveUsulanBukbel()
    {
      $dupe = $this->db('hosplan_bukbel')->where('no_faktor', $_POST['no_faktor'])->oneArray();
      if ($dupe) {
        redirect(url([ADMIN, 'hosplan', 'tambahusulanbukbel'], ['error' => 'nofaktor', 'id_rba' => $_POST['id_rba']]));
        return;
      }
      $this->db('hosplan_bukbel')->save([
        'id_rba' => $_POST['id_rba'],
        'uraian' => $_POST['uraian'],
        'tgl_faktor' => $_POST['tgl_faktor'],
        'no_faktor' => $_POST['no_faktor'],
        'toko' => $_POST['toko'],
        'jumlah' => $_POST['jumlah'],
        'status_spj' => $_POST['status_spj'],
        'keterangan' => $_POST['keterangan'],
      ]);

      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanbukbel'], ['rba' => $rba, 'added' => 1]));
    }

    public function getEditUsulanBukbel($id_bukbel) 
    {
      $this->_addHeaderFiles();
      
      $rba = $this->db('hosplan_rba')
            ->toArray();

      $usulanbukbel = $this->db('hosplan_bukbel') 
            ->join('hosplan_rba', 'hosplan_rba.id_rba=hosplan_bukbel.id_rba')
            ->where('hosplan_bukbel.id_bukbel', $id_bukbel)
            ->toArray();
    
      if (!$usulanbukbel) {
          return $this->draw('error.html', ['message' => 'Usulan Buku Belanja tidak ditemukan']);
      }
    
      return $this->draw('editusulanbukbel.html', [
          'usulanbukbel' => $usulanbukbel[0],
          'rba'  => $rba,
      ]);
    }

    public function postUpdateUsulanBukbel()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_bukbel'])) {
          redirect(url([ADMIN, 'hosplan', 'usulanbukbel']));  
          return;
      }

      $id_bukbel = $_POST['id_bukbel']; 

      $cek = $this->db('hosplan_bukbel')->where('id_bukbel', $id_bukbel)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanbukbel']));
          return;
      }

      $dupe = $this->db('hosplan_bukbel')->where('no_faktor', $_POST['no_faktor'])->oneArray();
      if ($dupe && isset($dupe['id_bukbel']) && (int)$dupe['id_bukbel'] !== (int)$id_bukbel) {
        redirect(url([ADMIN, 'hosplan', 'editusulanbukbel', $id_bukbel], ['error' => 'nofaktor']));
        return;
      }

      $this->db('hosplan_bukbel')
          ->where('id_bukbel', $id_bukbel)
          ->update([
              'id_bukbel' => $id_bukbel,  
              'id_rba' => $_POST['id_rba'],
              'uraian' => $_POST['uraian'],
              'tgl_faktor' => $_POST['tgl_faktor'],
              'no_faktor' => $_POST['no_faktor'],
              'toko' => $_POST['toko'],
              'jumlah' => $_POST['jumlah'],
              'status_spj' => $_POST['status_spj'],
              'keterangan' => $_POST['keterangan'],
          ]);


      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      redirect(url([ADMIN, 'hosplan', 'usulanbukbel'], ['rba' => $rba]));
    }

    public function getDeleteUsulanBukbel($id_bukbel)
    {
      $cek = $this->db('hosplan_bukbel')->where('id_bukbel', $id_bukbel)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'usulanbukbel']));
          return;
      }
      $this->db('hosplan_bukbel')->where('id_bukbel', $id_bukbel)->delete();
    
      $rba = isset($_GET['rba']) ? $_GET['rba'] : ''; 

      // Set flashdata sukses hapus
      $_SESSION['flashdata']['danger'] = 'Data berhasil dihapus!';

      redirect(url([ADMIN, 'hosplan', 'usulanbukbel'], ['rba' => $rba]));  
    }

    public function getCheckNoFaktorBukbel()
    {
      $nf = isset($_GET['no_faktor']) ? $_GET['no_faktor'] : '';
      $exclude = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;
      $q = $this->db('hosplan_bukbel')->where('no_faktor', $nf);
      if ($exclude > 0) { $q->where('id_bukbel != ', $exclude); }
      $row = $q->oneArray();
      header('Content-Type: application/json');
      echo json_encode(['exists' => (bool)$row]);
      exit;
    }

    public function postCetakUsulanBukbel($id_bukbel = null)
    {
      // Siapkan referensi untuk view
      $rba = $this->db('hosplan_rba')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Ambil data dari POST atau dari redirect session bila ada
      $payload = $_POST;
      if (empty($payload)) {
        $redirectData = getRedirectData();
        if (!empty($redirectData) && is_array($redirectData)) {
          $payload = $redirectData;
        }
      }

      // Alternatif: dukungan GET ?ids=31,35
      if (empty($payload['id_bukbel']) && isset($_GET['ids'])) {
        $raw = $_GET['ids'];
        $list = preg_split('/[\s,]+/', $raw);
        $payload['id_bukbel'] = array_filter(array_map('intval', $list));
      }

      if (isset($payload['id_bukbel'])) {
        $ids = $payload['id_bukbel'];
        if (!is_array($ids)) { $ids = [$ids]; }
        $ids = array_map('intval', $ids);

        $q = $this->db('hosplan_bukbel')
          ->join('hosplan_rba', 'hosplan_rba.id_rba=hosplan_bukbel.id_rba');

        $q->where(function($qq) use ($ids) {
          foreach ($ids as $id) {
            $qq->orWhere('hosplan_bukbel.id_bukbel', (int)$id);
          }
        });

        $rows = $q->toArray();

        echo $this->draw('cetakusulanbukbel.html', [
          'usulanbukbel' => $rows,
          'rba'  => $rba,
          'pptk'  => $pptk,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
        exit();
      }

      if ($id_bukbel !== null) {
        $usulanbukbel = $this->db('hosplan_bukbel')
          ->leftJoin('hosplan_rba', 'hosplan_rba.id_rba = hosplan_bukbel.id_rba')
          ->where('hosplan_bukbel.id_bukbel', '=', (int)$id_bukbel)
          ->toArray();

        echo $this->draw('cetakusulanbukbel.html', [
          'usulanbukbel' => $usulanbukbel,
          'rba'  => $rba,
          'pptk'  => $pptk,
          'ttd_files' => (function() {
            $ttd_files = [];
            $ttd_path = __DIR__ . '/img/ttd/';
            if (is_dir($ttd_path)) {
              $files = scandir($ttd_path);
              foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                  $ttd_files[] = $file;
                }
              }
            }
            return $ttd_files;
          })(),
        ]);
        exit();
      }
    }

    public function getrebel()
    {
      $this->_addHeaderFiles();

      $tabel_rebel = $this->db('hosplan_rebel')->toArray();

      return $this->draw('rebel.html', ['tabel_rebel' => $tabel_rebel]);
    }

    public function getEditRebel($id_rebel)
    {
      $this->_addHeaderFiles();
    
      $rebel = $this->db('hosplan_rebel')
          ->where('id_rebel', $id_rebel)
          ->toArray();
    
      return $this->draw('editrebel.html', [
          'rebel' => $rebel[0],
      ]);
    }

    public function postUpdateRebel()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_rebel'])) {
          redirect(url([ADMIN, 'hosplan', 'rebel']));  
          return;
      }

      $id_rebel = $_POST['id_rebel']; 

      $cek = $this->db('hosplan_rebel')->where('id_rebel', $id_rebel)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rebel']));
          return;
      }

      $this->db('hosplan_rebel')
          ->where('id_rebel', $id_rebel)
          ->update([
              'id_rebel' => $id_rebel,
              'kode_akun' => $_POST['kode_akun'],
              'uraian_kegiatan' => $_POST['uraian_kegiatan'],
              'januari' => $_POST['januari'],
              'februari' => $_POST['februari'],
              'maret' => $_POST['maret'],
              'april' => $_POST['april'],
              'mei' => $_POST['mei'],
              'juni' => $_POST['juni'],
              'juli' => $_POST['juli'],
              'agustus' => $_POST['agustus'],
              'september' => $_POST['september'],
              'oktober' => $_POST['oktober'],
              'november' => $_POST['november'],
              'desember' => $_POST['desember'],
          ]);

      $rebel = isset($_GET['rebel']) ? $_GET['rebel'] : '';
      
      redirect(url([ADMIN, 'hosplan', 'rebel'], ['rebel' => $rebel]));
    }

    public function postAjaxUpdateRebel()
    {
      $id_rebel = $_POST['id'];
      $field = $_POST['field'];
      $value = $_POST['value'];

      // Validate field name
      $allowed_fields = ['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'];
      if (!in_array($field, $allowed_fields)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid field']);
        exit;
      }

      // Remove non-numeric characters (assuming integers)
      $clean_value = preg_replace('/[^0-9]/', '', $value);

      $update = $this->db('hosplan_rebel')
        ->where('id_rebel', $id_rebel)
        ->update([
            $field => $clean_value
        ]);

      if ($update) {
        echo json_encode(['status' => 'success']);
      } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
      }
      exit;
    }

    public function getDeleteRebel($id_rebel)
    {
      $cek = $this->db('hosplan_rebel')->where('id_rebel', $id_rebel)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rebel']));
          return;
      }
      $this->db('hosplan_rebel')->where('id_rebel', $id_rebel)->delete();
    
      $rebel = isset($_GET['rebel']) ? $_GET['rebel'] : '';
      redirect(url([ADMIN, 'hosplan', 'rebel'], ['rebel' => $rebel]));
    }

    // Upload Usulan

    public function getUpload()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'Upload Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'uploadusulanrka']), 'icon' => 'clipboard', 'desc' => 'Upload Data Usulan RKA'],
        ['name' => 'Upload Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'uploadusulanrba']), 'icon' => 'clipboard', 'desc' => 'Upload Data Usulan RBA'],
        ['name' => 'Upload Usulan Bukbel', 'url' => url([ADMIN, 'hosplan', 'uploadusulanbukbel']), 'icon' => 'book', 'desc' => 'Upload Data Usulan Buku Belanja'],
        // ['name' => 'Arsip Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'arsipusulanrka']), 'icon' => 'clipboard', 'desc' => 'Arsip Data Usulan RKA'],
        // ['name' => 'Arsip Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'arsipusulanrba']), 'icon' => 'clipboard', 'desc' => 'Arsip Data Usulan RBA'],
        // ['name' => 'Arsip Usulan Buku Belanja', 'url' => url([ADMIN, 'hosplan', 'arsipusulanbukbel']), 'icon' => 'book', 'desc' => 'Arsip Data Usulan Buku Belanja'],
      ];

      // Hitung jumlah upload berdasarkan tabel masing-masing
      $rkaRows = $this->db('hosplan_uploadrka')->toArray();
      $rbaRows = $this->db('hosplan_uploadrba')->toArray();
      $bukbelRows = $this->db('hosplan_uploadbukbel')->toArray();

      $jumlahUploadRKA = is_array($rkaRows) ? count($rkaRows) : 0;
      $jumlahUploadRBA = is_array($rbaRows) ? count($rbaRows) : 0;
      $jumlahUploadBukbel = is_array($bukbelRows) ? count($bukbelRows) : 0;

      return $this->draw('upload.html', [
        'sub_modules' => $sub_modules,
        'jumlahUploadRKA' => $jumlahUploadRKA,
        'jumlahUploadRBA' => $jumlahUploadRBA,
        'jumlahUploadBukbel' => $jumlahUploadBukbel,
      ]);
    }

    public function getUploadUsulanRKA()
    {
      $this->_addHeaderFiles();

      $surat = $this->db('hosplan_surat')->toArray();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      $userUnitId = $this->_getUserUnitId();

      $query = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat=hosplan_uploadrka.id_surat')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_uploadrka.id_unit');

      if ($userUnitId) {
        $query->where('hosplan_uploadrka.id_unit', $userUnitId);
      }

      $tabel_uploadusulanrka = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/../../uploads/hosplan/usulanrka/';
      $pluginWeb = '/uploads/hosplan/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrka)) {
        foreach ($tabel_uploadusulanrka as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRKA = is_array($tabel_uploadusulanrka) ? count($tabel_uploadusulanrka) : 0;
      $isUnitSelected = ($userUnitId && $userUnitId > 0);

      return $this->draw('uploadusulanrka.html', [
        'tabel_uploadusulanrka' => $tabel_uploadusulanrka,
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit,
        'selectedUnit' => $userUnitId ?: 'all',
        'totalUploadRKA' => $totalUploadRKA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getTambahUploadRKA()
    {
      // $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $surat = $this->db('hosplan_surat')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      return $this->draw('tambahuploadrka.html', [
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit]);
    }

    public function postSaveUploadRKA()
    {
      // Proses upload file (PDF/DOC/DOCX) dengan batas 5 MB
      $uploaded_name = '';
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          // Redirect kembali ke form dengan pesan error via query string
          redirect(url([ADMIN, 'hosplan', 'tambahuploadrka'], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'RKA_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanrka/';
          // pastikan folder tersedia
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadrka')->save([
        'id_surat' => $_POST['id_surat'],
        'tanggal_usulan' => $_POST['tanggal_usulan'],
        'id_jenis_usulan' => $_POST['id_jenis_usulan'],
        'id_status_surat' => $_POST['id_status_surat'],
        'file_usulan' => $uploaded_name,
        'keterangan' => $_POST['keterangan'],
        'id_unit' => $_POST['id_unit'],
      ]);

      $surat = isset($_GET['surat']) ? $_GET['surat'] : '';
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrka'], ['surat' => $surat, 'jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'added' => 1]));
    }

    public function getEditUploadRKA($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $surat = $this->db('hosplan_surat')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();
    
      $uploadrka = $this->db('hosplan_uploadrka')
          ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat = hosplan_uploadrka.id_surat')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_uploadrka.id_unit')
          ->where('hosplan_uploadrka.id_usulan', $id_usulan)
          ->toArray();

      if (!$uploadrka) {
          return $this->draw('error.html', ['message' => 'Usulan RKA tidak ditemukan']);
      }

      // Tambahkan file_url untuk tampilan edit (fallback lokasi lama)
      $pluginFs = __DIR__ . '/uploads/usulanrka/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      $fn = isset($uploadrka[0]['file_usulan']) ? $uploadrka[0]['file_usulan'] : '';
      if ($fn) {
        if (is_file($pluginFs . $fn)) {
          $uploadrka[0]['file_url'] = $pluginWeb . $fn;
        } elseif (is_file($legacyFs . $fn)) {
          $uploadrka[0]['file_url'] = $legacyWeb . $fn;
        } else {
          $uploadrka[0]['file_url'] = '';
        }
      } else {
        $uploadrka[0]['file_url'] = '';
      }
    
      return $this->draw('edituploadrka.html', [
          'uploadrka' => $uploadrka[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'surat'  => $surat,
          'unit'  => $unit,

      ]);
    }

    public function postUpdateUploadRKA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrka')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));
          return;
      }

      // Tangani upload file baru jika ada, jika tidak pertahankan yang lama
      $uploaded_name = $cek['file_usulan'];
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          // Ukuran terlalu besar, kembali ke form edit dengan pesan
          redirect(url([ADMIN, 'hosplan', 'edituploadrka', $id_usulan], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'RKA_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanrka/';
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadrka')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_surat' => $_POST['id_surat'],
              'tanggal_usulan' => $_POST['tanggal_usulan'],
              'id_jenis_usulan' => $_POST['id_jenis_usulan'],
              'id_status_surat' => $_POST['id_status_surat'],
              'file_usulan' => $uploaded_name,
              'keterangan' => $_POST['keterangan'],
              'id_unit' => $_POST['id_unit'],
          ]);

      $surat = isset($_GET['surat']) ? $_GET['surat'] : '';
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrka'], ['surat' => $surat, 'jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getDeleteUploadRKA($id_usulan)
    {
      $cek = $this->db('hosplan_uploadrka')->where('id_usulan', $id_usulan)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));
          return;
      }
      $this->db('hosplan_uploadrka')->where('id_usulan', $id_usulan)->delete();
    
      $surat = isset($_GET['surat']) ? $_GET['surat'] : '';
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['deleted'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrka'], ['surat' => $surat, 'jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'deleted' => 1]));
    }

    // Upload RBA

    public function getUploadUsulanRBA()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadrba.id_pptk', $selectedPptk);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi RBA
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanrba = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/../../uploads/hosplan/usulanrba/';
      $pluginWeb = '/uploads/hosplan/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrba)) {
        foreach ($tabel_uploadusulanrba as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRBA = is_array($tabel_uploadusulanrba) ? count($tabel_uploadusulanrba) : 0;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('uploadusulanrba.html', [
        'tabel_uploadusulanrba' => $tabel_uploadusulanrba,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadRBA' => $totalUploadRBA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getTambahUploadRBA()
    {
      // $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      return $this->draw('tambahuploadrba.html', [
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk]);
    }

    public function postSaveUploadRBA()
    {
      // Proses upload file (PDF/DOC/DOCX) dengan batas 5 MB
      $uploaded_name = '';
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          // Redirect kembali ke form dengan pesan error via query string
          redirect(url([ADMIN, 'hosplan', 'tambahuploadrka'], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'RBA_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanrba/';
          // pastikan folder tersedia
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadrba')->save([
        'no_surat' => $_POST['no_surat'],
        'tanggal_usulan' => $_POST['tanggal_usulan'],
        'perihal' => $_POST['perihal'],
        'id_jenis_usulan' => $_POST['id_jenis_usulan'],
        'id_status_surat' => $_POST['id_status_surat'],
        'file_usulan' => $uploaded_name,
        'keterangan' => $_POST['keterangan'],
        'id_pptk' => $_POST['id_pptk'],
      ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrba'], ['jenis' => $jenis, 'status' => $status, 'pptk' => $pptk, 'added' => 1]));
    }

    public function getEditUploadRBA($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();
    
      $uploadrba = $this->db('hosplan_uploadrba')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
          ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk = hosplan_uploadrba.id_pptk')
          ->where('hosplan_uploadrba.id_usulan', $id_usulan)
          ->toArray();

      if (!$uploadrba) {
          return $this->draw('error.html', ['message' => 'Usulan RBA tidak ditemukan']);
      }

      // Tambahkan file_url untuk tampilan edit (fallback lokasi lama)
      $pluginFs = __DIR__ . '/uploads/usulanrba/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      $fn = isset($uploadrba[0]['file_usulan']) ? $uploadrba[0]['file_usulan'] : '';
      if ($fn) {
        if (is_file($pluginFs . $fn)) {
          $uploadrba[0]['file_url'] = $pluginWeb . $fn;
        } elseif (is_file($legacyFs . $fn)) {
          $uploadrba[0]['file_url'] = $legacyWeb . $fn;
        } else {
          $uploadrba[0]['file_url'] = '';
        }
      } else {
        $uploadrba[0]['file_url'] = '';
      }
    
      return $this->draw('edituploadrba.html', [
          'uploadrba' => $uploadrba[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'pptk'  => $pptk,

      ]);
    }

    public function postUpdateUploadRBA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrba')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));
          return;
      }

      // Tangani upload file baru jika ada, jika tidak pertahankan yang lama
      $uploaded_name = $cek['file_usulan'];
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          // Ukuran terlalu besar, kembali ke form edit dengan pesan
          redirect(url([ADMIN, 'hosplan', 'edituploadrba', $id_usulan], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'RKA_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanrba/';
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadrba')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'no_surat' => $_POST['no_surat'],
              'tanggal_usulan' => $_POST['tanggal_usulan'],
              'perihal' => $_POST['perihal'],
              'id_jenis_usulan' => $_POST['id_jenis_usulan'],
              'id_status_surat' => $_POST['id_status_surat'],
              'file_usulan' => $uploaded_name,
              'keterangan' => $_POST['keterangan'],
              'id_pptk' => $_POST['id_pptk'],
          ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrba'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    // Upload Bukbel (mirip RBA)
    public function getUploadUsulanBukbel()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter PPTK: dukung "Semua PPTK" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadbukbel.id_pptk', $selectedPptk);
      }

      $tabel_uploadusulanbukbel = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/../../uploads/hosplan/usulanbukbel/';
      $pluginWeb = '/uploads/hosplan/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanbukbel)) {
        foreach ($tabel_uploadusulanbukbel as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadBukbel = is_array($tabel_uploadusulanbukbel) ? count($tabel_uploadusulanbukbel) : 0;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('uploadusulanbukbel.html', [
        'tabel_uploadusulanbukbel' => $tabel_uploadusulanbukbel,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadBukbel' => $totalUploadBukbel,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getTambahUploadBukbel()
    {
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      return $this->draw('tambahuploadbukbel.html', [
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
      ]);
    }

    public function postSaveUploadBukbel()
    {
      // Proses upload file (PDF/DOC/DOCX) dengan batas 5 MB
      $uploaded_name = '';
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          redirect(url([ADMIN, 'hosplan', 'tambahuploadbukbel'], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'BUKBEL_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanbukbel/';
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadbukbel')->save([
        'no_surat' => $_POST['no_surat'],
        'tanggal_usulan' => $_POST['tanggal_usulan'],
        'perihal' => $_POST['perihal'],
        'id_jenis_usulan' => $_POST['id_jenis_usulan'],
        'id_status_surat' => $_POST['id_status_surat'],
        'file_usulan' => $uploaded_name,
        'keterangan' => $_POST['keterangan'],
        'id_pptk' => $_POST['id_pptk'],
      ]);

      $pptk = isset($_POST['id_pptk']) ? $_POST['id_pptk'] : (isset($_GET['id_pptk']) ? $_GET['id_pptk'] : '');
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel'], ['id_pptk' => $pptk, 'added' => 1]));
    }

    public function getEditUploadBukbel($id_usulan)
    {
      $this->_addHeaderFiles();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      $upload = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk')
        ->where('hosplan_uploadbukbel.id_usulan', $id_usulan)
        ->toArray();

      if (!$upload) {
        return $this->draw('error.html', ['message' => 'Upload Bukbel tidak ditemukan']);
      }

      // Tambahkan file_url untuk tampilan edit (fallback lokasi lama)
      $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      $fn = isset($upload[0]['file_usulan']) ? $upload[0]['file_usulan'] : '';
      if ($fn) {
        if (is_file($pluginFs . $fn)) {
          $upload[0]['file_url'] = $pluginWeb . $fn;
        } elseif (is_file($legacyFs . $fn)) {
          $upload[0]['file_url'] = $legacyWeb . $fn;
        } else {
          $upload[0]['file_url'] = '';
        }
      } else {
        $upload[0]['file_url'] = '';
      }

      return $this->draw('edituploadbukbel.html', [
        'uploadbukbel' => $upload[0],
        'status' => $status,
        'jenis' => $jenis,
        'pptk' => $pptk,
      ]);
    }

    public function postUpdateUploadBukbel()
    {
      if (!isset($_POST['id_usulan'])) {
        redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel']));
        return;
      }
      $id_usulan = $_POST['id_usulan'];
      $cek = $this->db('hosplan_uploadbukbel')->where('id_usulan', $id_usulan)->oneArray();
      if (!$cek) {
        redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel']));
        return;
      }

      // Tangani file baru jika diunggah
      $uploaded_name = $cek['file_usulan'];
      if (isset($_FILES['file_usulan']) && isset($_FILES['file_usulan']['tmp_name']) && is_uploaded_file($_FILES['file_usulan']['tmp_name'])) {
        $maxSize = 5 * 1024 * 1024; // 5 MB
        if (isset($_FILES['file_usulan']['size']) && $_FILES['file_usulan']['size'] > $maxSize) {
          redirect(url([ADMIN, 'hosplan', 'edituploadbukbel', $id_usulan], ['error' => 'size']));
          return;
        }
        $ext = strtolower(pathinfo($_FILES['file_usulan']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx'];
        if (in_array($ext, $allowed)) {
          $safeBase = preg_replace('/[^A-Za-z0-9_.-]/', '_', pathinfo($_FILES['file_usulan']['name'], PATHINFO_FILENAME));
          $newFileName = 'BUKBEL_' . time() . '_' . $safeBase . '.' . $ext;
          $targetFolder = __DIR__ . '/../../uploads/hosplan/usulanbukbel/';
          @mkdir($targetFolder, 0777, true);
          if (move_uploaded_file($_FILES['file_usulan']['tmp_name'], $targetFolder . $newFileName)) {
            $uploaded_name = $newFileName;
          }
        }
      }

      $this->db('hosplan_uploadbukbel')
        ->where('id_usulan', $id_usulan)
        ->update([
          'id_usulan' => $id_usulan,
          'no_surat' => $_POST['no_surat'],
          'tanggal_usulan' => $_POST['tanggal_usulan'],
          'perihal' => $_POST['perihal'],
          'id_jenis_usulan' => $_POST['id_jenis_usulan'],
          'id_status_surat' => $_POST['id_status_surat'],
          'file_usulan' => $uploaded_name,
          'keterangan' => $_POST['keterangan'],
          'id_pptk' => $_POST['id_pptk'],
        ]);

      $pptk = isset($_POST['id_pptk']) ? $_POST['id_pptk'] : (isset($cek['id_pptk']) ? $cek['id_pptk'] : (isset($_GET['id_pptk']) ? $_GET['id_pptk'] : ''));
      $_SESSION['flashdata']['edited'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel'], ['id_pptk' => $pptk, 'edited' => 1]));
    }

    public function getDeleteUploadBukbel($id_usulan)
    {
      $cek = $this->db('hosplan_uploadbukbel')->where('id_usulan', $id_usulan)->oneArray();
      if (!$cek) {
        redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel']));
        return;
      }
      $this->db('hosplan_uploadbukbel')->where('id_usulan', $id_usulan)->delete();
      $pptk = isset($cek['id_pptk']) ? $cek['id_pptk'] : (isset($_GET['id_pptk']) ? $_GET['id_pptk'] : '');
      redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel'], ['id_pptk' => $pptk, 'deleted' => 1]));
    }

    public function getDeleteUploadRBA($id_usulan)
    {
      $cek = $this->db('hosplan_uploadrba')->where('id_usulan', $id_usulan)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));
          return;
      }
      $this->db('hosplan_uploadrba')->where('id_usulan', $id_usulan)->delete();
    
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['deleted'] = true;
      redirect(url([ADMIN, 'hosplan', 'uploadusulanrba'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'deleted' => 1]));
    }

    public function getKonfirmasi()
    {
      $this->_addHeaderFiles();

      // Hitung jumlah upload total dan jumlah status Diajukan
      $rkaRows = $this->db('hosplan_uploadrka')->toArray();
      $rbaRows = $this->db('hosplan_uploadrba')->toArray();
      $bukbelRows = $this->db('hosplan_uploadbukbel')->toArray();

      $jumlahUploadRKA = is_array($rkaRows) ? count($rkaRows) : 0;
      $jumlahUploadRBA = is_array($rbaRows) ? count($rbaRows) : 0;
      $jumlahUploadBukbel = is_array($bukbelRows) ? count($bukbelRows) : 0;

      $pendingRKA = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();
      $pendingRBA = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();
      $pendingBukbel = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();

      $sub_modules = [
        ['name' => 'Konfirmasi Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanrka']), 'icon' => 'clipboard', 'desc' => 'Konfirmasi Data Usulan RKA', 'badge' => (is_array($pendingRKA) ? count($pendingRKA) : 0)],
        ['name' => 'Konfirmasi Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanrba']), 'icon' => 'clipboard', 'desc' => 'Konfirmasi Data Usulan RBA', 'badge' => (is_array($pendingRBA) ? count($pendingRBA) : 0)],
        ['name' => 'Konfirmasi Usulan BukBel', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanbukbel']), 'icon' => 'book', 'desc' => 'Konfirmasi Data Usulan Buku Belanja', 'badge' => (is_array($pendingBukbel) ? count($pendingBukbel) : 0)],
      ];

      return $this->draw('konfirmasi.html', [
        'sub_modules' => $sub_modules,
        'jumlahUploadRKA' => $jumlahUploadRKA,
        'jumlahUploadRBA' => $jumlahUploadRBA,
        'jumlahUploadBukbel' => $jumlahUploadBukbel,
        'pendingRKA' => (is_array($pendingRKA) ? count($pendingRKA) : 0),
        'pendingRBA' => (is_array($pendingRBA) ? count($pendingRBA) : 0),
        'pendingBukbel' => (is_array($pendingBukbel) ? count($pendingBukbel) : 0),
      ]);
    }

    public function getKonfirmasiUsulanRKA()
    {
      $this->_addHeaderFiles();

      $surat = $this->db('hosplan_surat')->toArray();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedUnitRaw = isset($_GET['id_unit']) ? $_GET['id_unit'] : 'all';
      $selectedUnit = is_numeric($selectedUnitRaw) ? (int) $selectedUnitRaw : $selectedUnitRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat=hosplan_uploadrka.id_surat')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_uploadrka.id_unit');

      if ($selectedUnit !== 'all' && is_int($selectedUnit)) {
        $query->where('hosplan_uploadrka.id_unit', $selectedUnit);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanrka = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrka/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrka)) {
        foreach ($tabel_uploadusulanrka as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRKA = is_array($tabel_uploadusulanrka) ? count($tabel_uploadusulanrka) : 0;
      $totalKonfirmasiRKA = $totalUploadRKA;
      $isUnitSelected = ($selectedUnit !== 'all' && is_int($selectedUnit) && $selectedUnit > 0);

      return $this->draw('konfirmasiusulanrka.html', [
        'tabel_uploadusulanrka' => $tabel_uploadusulanrka,
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit,
        'selectedUnit' => $selectedUnit,
        'totalUploadRKA' => $totalUploadRKA,
        'totalKonfirmasiRKA' => $totalKonfirmasiRKA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getKonfirmasiStatusRKA($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $surat = $this->db('hosplan_surat')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();
    
      $uploadrka = $this->db('hosplan_uploadrka')
          ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat = hosplan_uploadrka.id_surat')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_uploadrka.id_unit')
          ->where('hosplan_uploadrka.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status
      if ($uploadrka && isset($uploadrka[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanrka/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadrka[0]['file_usulan']) ? $uploadrka[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadrka[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadrka[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadrka[0]['file_url'] = '';
          }
        } else {
          $uploadrka[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusrka.html', [
          'uploadrka' => $uploadrka[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'surat'  => $surat,
          'unit'  => $unit,

      ]);
    }

    public function postUpdateStatusRKA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrka')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));
          return;
      }

      $this->db('hosplan_uploadrka')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $surat = isset($_GET['surat']) ? $_GET['surat'] : '';
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanrka'], ['surat' => $surat, 'jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getKonfirmasiUsulanRBA()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadrba.id_pptk', $selectedPptk);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanrba = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrba/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrba)) {
        foreach ($tabel_uploadusulanrba as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRBA = is_array($tabel_uploadusulanrba) ? count($tabel_uploadusulanrba) : 0;
      $totalKonfirmasiRBA = $totalUploadRBA;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('konfirmasiusulanrba.html', [
        'tabel_uploadusulanrba' => $tabel_uploadusulanrba,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadRBA' => $totalUploadRBA,
        'totalKonfirmasiRBA' => $totalKonfirmasiRBA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }  

    public function getKonfirmasiStatusRBA($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();
    
      $uploadrba = $this->db('hosplan_uploadrba')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
          ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk = hosplan_uploadrba.id_pptk')
          ->where('hosplan_uploadrba.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status RBA
      if ($uploadrba && isset($uploadrba[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanrba/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadrba[0]['file_usulan']) ? $uploadrba[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadrba[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadrba[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadrba[0]['file_url'] = '';
          }
        } else {
          $uploadrba[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusrba.html', [
          'uploadrba' => $uploadrba[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'pptk'  => $pptk,

      ]);
    }

    public function postUpdateStatusRBA()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrba')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));
          return;
      }

      $this->db('hosplan_uploadrba')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanrba'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getKonfirmasiUsulanBukbel()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadbukbel.id_pptk', $selectedPptk);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanbukbel = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanbukbel)) {
        foreach ($tabel_uploadusulanbukbel as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadBukbel = is_array($tabel_uploadusulanbukbel) ? count($tabel_uploadusulanbukbel) : 0;
      $totalKonfirmasiBukbel = $totalUploadBukbel;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('konfirmasiusulanbukbel.html', [
        'tabel_uploadusulanbukbel' => $tabel_uploadusulanbukbel,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadBukbel' => $totalUploadBukbel,
        'totalKonfirmasiBukbel' => $totalKonfirmasiBukbel,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getKonfirmasiStatusBukbel($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();
    
      $uploadbukbel = $this->db('hosplan_uploadbukbel')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
          ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk = hosplan_uploadbukbel.id_pptk')
          ->where('hosplan_uploadbukbel.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status Bukbel
      if ($uploadbukbel && isset($uploadbukbel[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadbukbel[0]['file_usulan']) ? $uploadbukbel[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadbukbel[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadbukbel[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadbukbel[0]['file_url'] = '';
          }
        } else {
          $uploadbukbel[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusbukbel.html', [
          'uploadbukbel' => $uploadbukbel[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'pptk'  => $pptk,
      ]);
    }

    public function postUpdateStatusBukbel()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadbukbel')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbel']));
          return;
      }

      $this->db('hosplan_uploadbukbel')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanbukbel'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getArsipSetuju()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'Usulan RKA Disetujui', 'url' => url([ADMIN, 'hosplan', 'usulanrkasetuju']), 'icon' => 'thumbs-up', 'desc' => 'Arsip Data Usulan RKA yang Disetujui'],
        ['name' => 'Usulan RBA Disetujui', 'url' => url([ADMIN, 'hosplan', 'usulanrbasetuju']), 'icon' => 'thumbs-up', 'desc' => 'Arsip Data Usulan RBA yang Disetujui'],
        ['name' => 'Usulan BukBel Disetujui', 'url' => url([ADMIN, 'hosplan', 'usulanbukbelsetuju']), 'icon' => 'thumbs-up', 'desc' => 'Arsip Data Usulan Buku Belanja yang Disetujui'],
      ];

      $rkaSetuju = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahRKASetuju = is_array($rkaSetuju) ? count($rkaSetuju) : 0;

      $rbaSetuju = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahRBASetuju = is_array($rbaSetuju) ? count($rbaSetuju) : 0;

      $bukbelSetuju = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahBukbelSetuju = is_array($bukbelSetuju) ? count($bukbelSetuju) : 0;

      return $this->draw('arsipsetuju.html', [
        'sub_modules' => $sub_modules,
        'jumlahRKASetuju' => $jumlahRKASetuju,
        'jumlahRBASetuju' => $jumlahRBASetuju,
        'jumlahBukbelSetuju' => $jumlahBukbelSetuju,
      ]);
    }

    public function getUsulanRKASetuju()
    {
      $this->_addHeaderFiles();

      $surat = $this->db('hosplan_surat')->toArray();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedUnitRaw = isset($_GET['id_unit']) ? $_GET['id_unit'] : 'all';
      $selectedUnit = is_numeric($selectedUnitRaw) ? (int) $selectedUnitRaw : $selectedUnitRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat=hosplan_uploadrka.id_surat')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_uploadrka.id_unit');

      if ($selectedUnit !== 'all' && is_int($selectedUnit)) {
        $query->where('hosplan_uploadrka.id_unit', $selectedUnit);
      }
      // Arsip usulan disetujui
      $query->where('hosplan_status.status_surat', 'Disetujui');

      $tabel_uploadusulanrka = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrka/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrka)) {
        foreach ($tabel_uploadusulanrka as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $rkaSetuju = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahRKASetuju = is_array($rkaSetuju) ? count($rkaSetuju) : 0;

      $isUnitSelected = ($selectedUnit !== 'all' && is_int($selectedUnit) && $selectedUnit > 0);

      return $this->draw('usulanrkasetuju.html', [
        'tabel_uploadusulanrka' => $tabel_uploadusulanrka,
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit,
        'selectedUnit' => $selectedUnit,
        'jumlahRKASetuju' => $jumlahRKASetuju,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getUsulanRKATolak()
    {
      $this->_addHeaderFiles();

      $surat = $this->db('hosplan_surat')->toArray();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedUnitRaw = isset($_GET['id_unit']) ? $_GET['id_unit'] : 'all';
      $selectedUnit = is_numeric($selectedUnitRaw) ? (int) $selectedUnitRaw : $selectedUnitRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat=hosplan_uploadrka.id_surat')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_uploadrka.id_unit');

      if ($selectedUnit !== 'all' && is_int($selectedUnit)) {
        $query->where('hosplan_uploadrka.id_unit', $selectedUnit);
      }
      // Arsip usulan ditolak
      $query->where('hosplan_status.status_surat', 'Ditolak');

      $tabel_uploadusulanrka = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrka/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrka)) {
        foreach ($tabel_uploadusulanrka as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $rkaTolak = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahRKATolak = is_array($rkaTolak) ? count($rkaTolak) : 0;
      
      $isUnitSelected = ($selectedUnit !== 'all' && is_int($selectedUnit) && $selectedUnit > 0);

      return $this->draw('usulanrkatolak.html', [
        'tabel_uploadusulanrka' => $tabel_uploadusulanrka,
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit,
        'selectedUnit' => $selectedUnit,
        'jumlahRKATolak' => $jumlahRKATolak,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getUsulanRBASetuju()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadrba.id_pptk', $selectedPptk);
      }
      // Arsip usulan disetujui
      $query->where('hosplan_status.status_surat', 'Disetujui');

      $tabel_uploadusulanrba = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrba/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrba)) {
        foreach ($tabel_uploadusulanrba as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $rbaSetuju = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahRBASetuju = is_array($rbaSetuju) ? count($rbaSetuju) : 0;
      $isPptkSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('usulanrbasetuju.html', [
        'tabel_uploadusulanrba' => $tabel_uploadusulanrba,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'jumlahRBASetuju' => $jumlahRBASetuju,
        'isPptkSelected' => $isPptkSelected,
      ]);
    }

    public function getUsulanRBATolak()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadrba.id_pptk', $selectedPptk);
      }
      // Arsip usulan ditolak
      $query->where('hosplan_status.status_surat', 'Ditolak');

      $tabel_uploadusulanrba = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrba/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrba)) {
        foreach ($tabel_uploadusulanrba as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $rbaTolak = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahRBATolak = is_array($rbaTolak) ? count($rbaTolak) : 0;
      $isPptkSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('usulanrbatolak.html', [
        'tabel_uploadusulanrba' => $tabel_uploadusulanrba,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'jumlahRBATolak' => $jumlahRBATolak,
        'isPptkSelected' => $isPptkSelected,
      ]);
    }

    public function getUsulanBukbelSetuju()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadbukbel.id_pptk', $selectedPptk);
      }
      // Arsip usulan disetujui
      $query->where('hosplan_status.status_surat', 'Disetujui');

      $tabel_uploadusulanbukbel = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanbukbel)) {
        foreach ($tabel_uploadusulanbukbel as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $bukbelSetuju = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Disetujui')
        ->toArray();
      $jumlahBukbelSetuju = is_array($bukbelSetuju) ? count($bukbelSetuju) : 0;
      $isPptkSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('usulanbukbelsetuju.html', [
        'tabel_uploadusulanbukbel' => $tabel_uploadusulanbukbel,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'jumlahBukbelSetuju' => $jumlahBukbelSetuju,
        'isPptkSelected' => $isPptkSelected,
      ]);
    }

    public function getUsulanBukbelTolak()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadbukbel.id_pptk', $selectedPptk);
      }
      // Arsip usulan ditolak
      $query->where('hosplan_status.status_surat', 'Ditolak');

      $tabel_uploadusulanbukbel = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanbukbel)) {
        foreach ($tabel_uploadusulanbukbel as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $bukbelTolak = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahBukbelTolak = is_array($bukbelTolak) ? count($bukbelTolak) : 0;
      $isPptkSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('usulanbukbeltolak.html', [
        'tabel_uploadusulanbukbel' => $tabel_uploadusulanbukbel,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'jumlahBukbelTolak' => $jumlahBukbelTolak,
        'isPptkSelected' => $isPptkSelected,
      ]);
    }

    public function getArsipTolak()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'Usulan RKA Ditolak', 'url' => url([ADMIN, 'hosplan', 'usulanrkatolak']), 'icon' => 'thumbs-down', 'desc' => 'Arsip Data Usulan RKA yang Ditolak'],
        ['name' => 'Usulan RBA Ditolak', 'url' => url([ADMIN, 'hosplan', 'usulanrbatolak']), 'icon' => 'thumbs-down', 'desc' => 'Arsip Data Usulan RBA yang Ditolak'],
        ['name' => 'Usulan Buku Belanja Ditolak', 'url' => url([ADMIN, 'hosplan', 'usulanbukbeltolak']), 'icon' => 'thumbs-down', 'desc' => 'Arsip Data Usulan Buku Belanja yang Ditolak'],
      ];

      $rkaTolak = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahRKATolak = is_array($rkaTolak) ? count($rkaTolak) : 0;

      $rbaTolak = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahRBATolak = is_array($rbaTolak) ? count($rbaTolak) : 0;

      $bukbelTolak = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Ditolak')
        ->toArray();
      $jumlahBukbelTolak = is_array($bukbelTolak) ? count($bukbelTolak) : 0;

      return $this->draw('arsiptolak.html', [
        'sub_modules' => $sub_modules,
        'jumlahRKATolak' => $jumlahRKATolak,
        'jumlahRBATolak' => $jumlahRBATolak,
        'jumlahBukbelTolak' => $jumlahBukbelTolak,
      ]);
    }

    public function getMaster()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'RBA', 'url' => url([ADMIN, 'hosplan', 'menurba']), 'icon' => 'code', 'desc' => 'Data RBA'],
        ['name' => 'Akun Belanja', 'url' => url([ADMIN, 'hosplan', 'akunbelanja']), 'icon' => 'book', 'desc' => 'Data Akun Belanja'],
        ['name' => 'Unit', 'url' => url([ADMIN, 'hosplan', 'unit']), 'icon' => 'home', 'desc' => 'Data Unit'],
        ['name' => 'PPTK', 'url' => url([ADMIN, 'hosplan', 'pptk']), 'icon' => 'user', 'desc' => 'Data PPTK'],
        ['name' => 'Konfirmasi Usulan Edit', 'url' => url([ADMIN, 'hosplan', 'konfirmasiedit']), 'icon' => 'thumbs-up', 'desc' => 'Konfirmasi Usulan Edit'],
      ];

      return $this->draw('master.html', ['sub_modules' => $sub_modules]);
    }

    // Menu RBA
    public function getMenuRba()
    {
      $this->_addHeaderFiles();

      $sub_modules = [
        ['name' => 'RBA 2025', 'url' => url([ADMIN, 'hosplan', 'rba']), 'icon' => 'code', 'desc' => 'Data RBA 2025'],
        ['name' => 'RBA 2026', 'url' => url([ADMIN, 'hosplan', 'rba2026']), 'icon' => 'code', 'desc' => 'Data RBA 2026'],
      ];

      return $this->draw('menurba.html', ['sub_modules' => $sub_modules]);
    }

    public function getRba()
    {
      $this->_addHeaderFiles();

      $tabel_rba = $this->db('hosplan_rba')
            ->toArray();

      return $this->draw('rba.html', ['tabel_rba' => $tabel_rba]);
    }

    public function getTambahRba()
    {
      $this->_addHeaderFiles();

      $rba = $this->db('hosplan_rba')
            ->toArray();

      return $this->draw('tambahrba.html', ['rba' => $rba]);
    }

    public function postSaveRba()
    {
      $this->db('hosplan_rba')->save([
        'kode_akun' => $_POST['kode_akun'],
        'uraian_kegiatan' => $_POST['uraian_kegiatan'],
        'anggaran' => $_POST['anggaran'],
        'perubahan' => $_POST['perubahan'],
        'pergeseran' => $_POST['pergeseran'],
        'realisasi' => $_POST['realisasi'],
      ]);

      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba'], ['rba' => $rba]));
    }
    
    public function getEditRba($id_rba)
    {
      $this->_addHeaderFiles();
    
      $rba = $this->db('hosplan_rba')
          ->where('id_rba', $id_rba)
          ->toArray();
    
      return $this->draw('editrba.html', [
          'rba' => $rba[0],
      ]);
    }

    public function postUpdateRba()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_rba'])) {
          redirect(url([ADMIN, 'hosplan', 'rba']));  
          return;
      }

      $id_rba = $_POST['id_rba'];

      $cek = $this->db('hosplan_rba')->where('id_rba', $id_rba)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rba']));
          return;
      }

      $this->db('hosplan_rba')
          ->where('id_rba', $id_rba)
          ->update([
              'id_rba' => $id_rba,
              'kode_akun' => $_POST['kode_akun'],
              'uraian_kegiatan' => $_POST['uraian_kegiatan'],
              'anggaran' => $_POST['anggaran'],
              'perubahan' => $_POST['perubahan'],
              'pergeseran' => $_POST['pergeseran'],
              'realisasi' => $_POST['realisasi'],
          ]);

      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba'], ['rba' => $rba]));
    }

    public function getDeleteRba($id_rba)
    {
      $cek = $this->db('hosplan_rba')->where('id_rba', $id_rba)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rba']));
          return;
      }
      $this->db('hosplan_rba')->where('id_rba', $id_rba)->delete();
    
      $rba = isset($_GET['rba']) ? $_GET['rba'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba'], ['rba' => $rba]));
    }

    public function getRba2026()
    {
      $this->_addHeaderFiles();

      $tabel_rba_2026 = $this->db('hosplan_rba_2026')
            ->toArray();

      return $this->draw('rba2026.html', ['tabel_rba_2026' => $tabel_rba_2026]);
    }

    public function getTambahRba2026()
    {
      $this->_addHeaderFiles();

      $rba2026 = $this->db('hosplan_rba_2026')
            ->toArray();

      return $this->draw('tambahrba2026.html', ['rba2026' => $rba2026]);
    }

    public function postSaveRba2026()
    {
      $this->db('hosplan_rba_2026')->save([
        'kode_akun' => $_POST['kode_akun'],
        'uraian_kegiatan' => $_POST['uraian_kegiatan'],
        'anggaran' => $_POST['anggaran'],
        'perubahan' => $_POST['perubahan'],
        'pergeseran' => $_POST['pergeseran'],
        'realisasi' => $_POST['realisasi'],
      ]);

      $rba2026 = isset($_GET['rba2026']) ? $_GET['rba2026'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba2026'], ['rba2026' => $rba2026]));
    }
    
    public function getEditRba2026($id_rba)
    {
      $this->_addHeaderFiles();
    
      $rba = $this->db('hosplan_rba_2026')
          ->where('id_rba', $id_rba)
          ->toArray();
    
      return $this->draw('editrba2026.html', [
          'rba' => $rba[0],
      ]);
    }

    public function postUpdateRba2026()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_rba'])) {
          redirect(url([ADMIN, 'hosplan', 'rba2026']));  
          return;
      }

      $id_rba = $_POST['id_rba'];

      $cek = $this->db('hosplan_rba_2026')->where('id_rba', $id_rba)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rba2026']));
          return;
      }

      $this->db('hosplan_rba_2026')
          ->where('id_rba', $id_rba)
          ->update([
              'id_rba' => $id_rba,
              'kode_akun' => $_POST['kode_akun'],
              'uraian_kegiatan' => $_POST['uraian_kegiatan'],
              'anggaran' => $_POST['anggaran'],
              'perubahan' => $_POST['perubahan'],
              'pergeseran' => $_POST['pergeseran'],
              'realisasi' => $_POST['realisasi'],
          ]);

      $rba2026 = isset($_GET['rba2026']) ? $_GET['rba2026'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba2026'], ['rba2026' => $rba2026]));
    }

    public function getDeleteRba2026($id_rba)
    {
      $cek = $this->db('hosplan_rba_2026')->where('id_rba', $id_rba)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'rba2026']));
          return;
      }
      $this->db('hosplan_rba_2026')->where('id_rba', $id_rba)->delete();
    
      $rba2026 = isset($_GET['rba2026']) ? $_GET['rba2026'] : '';
      redirect(url([ADMIN, 'hosplan', 'rba2026'], ['rba2026' => $rba2026]));
    }

    public function getAkunBelanja()
    {
      $this->_addHeaderFiles();

      $tabel_akunbelanja = $this->db('hosplan_kodebelanja')
            ->toArray();

      return $this->draw('akunbelanja.html', ['tabel_akunbelanja' => $tabel_akunbelanja]);
    }

    public function getTambahAkunBelanja()
    {
      $this->_addHeaderFiles();

      $akunbelanja = $this->db('hosplan_kodebelanja')
            ->toArray();

      return $this->draw('tambahakunbelanja.html', ['akunbelanja' => $akunbelanja]);
    }

    public function postSaveAkunBelanja()
    {
      $this->db('hosplan_kodebelanja')->save([
        'kode_kategori_baru' => $_POST['kode_kategori_baru'],
        'uraian_kategori' => $_POST['uraian_kategori'],
        'akun_belanja' => $_POST['akun_belanja'],
        'uraian_akun' => $_POST['uraian_akun'],
        'kelompok' => $_POST['kelompok'],
        'kode_kelompok' => $_POST['kode_kelompok'],
        'len' => $_POST['len'],
      ]);

      $akunbelanja = isset($_GET['akunbelanja']) ? $_GET['akunbelanja'] : '';
      redirect(url([ADMIN, 'hosplan', 'akunbelanja'], ['akunbelanja' => $akunbelanja]));
    }
    
    public function getEditAkunBelanja($id_kode)
    {
      $this->_addHeaderFiles();
    
      $akunbelanja = $this->db('hosplan_kodebelanja')
          ->where('id_kode', $id_kode)
          ->toArray();
    
      return $this->draw('editakunbelanja.html', [
          'akunbelanja' => $akunbelanja[0],
      ]);
    }

    public function postUpdateAkunBelanja()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_kode'])) {
          redirect(url([ADMIN, 'hosplan', 'akunbelanja']));  
          return;
      }

      $id_kode = $_POST['id_kode'];

      $cek = $this->db('hosplan_kodebelanja')->where('id_kode', $id_kode)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'akunbelanja']));
          return;
      }

      $this->db('hosplan_kodebelanja')
          ->where('id_kode', $id_kode)  
          ->update([
              'id_kode' => $id_kode,
              'kode_kategori_baru' => $_POST['kode_kategori_baru'],
              'uraian_kategori' => $_POST['uraian_kategori'],
              'akun_belanja' => $_POST['akun_belanja'],
              'uraian_akun' => $_POST['uraian_akun'],
              'kelompok' => $_POST['kelompok'],
              'kode_kelompok' => $_POST['kode_kelompok'],
              'len' => $_POST['len'],
          ]);

      $akunbelanja = isset($_GET['akunbelanja']) ? $_GET['akunbelanja'] : '';
      redirect(url([ADMIN, 'hosplan', 'akunbelanja'], ['akunbelanja' => $akunbelanja]));
    }

    public function getDeleteAkunBelanja($id_kode)
    {
      $cek = $this->db('hosplan_kodebelanja')->where('id_kode', $id_kode)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'akunbelanja']));
          return;
      }
      $this->db('hosplan_kodebelanja')->where('id_kode', $id_kode)->delete();
    
      $akunbelanja = isset($_GET['akunbelanja']) ? $_GET['akunbelanja'] : '';
      redirect(url([ADMIN, 'hosplan', 'akunbelanja'], ['akunbelanja' => $akunbelanja]));
    }

    public function getUnit()
    {
      $this->_addHeaderFiles();

      $tabel_unit = $this->db('hosplan_unit')
            ->toArray();

      return $this->draw('unit.html', ['tabel_unit' => $tabel_unit]);
    }

    public function getTambahUnit()
    {
      $this->_addHeaderFiles();

      $unit = $this->db('hosplan_unit')
            ->toArray();

      return $this->draw('tambahunit.html', ['unit' => $unit]);
    }

    public function postSaveUnit()
    {
      $this->db('hosplan_unit')->save([
        'nama_unit' => $_POST['nama_unit'],
        'jabatan' => $_POST['jabatan'],
        'nama_pegawai' => $_POST['nama_pegawai'],
        'nip' => $_POST['nip'],
      ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'unit'], ['unit' => $unit]));
    }
    
    public function getEditUnit($id_unit)
    {
      $this->_addHeaderFiles();
    
      $unit = $this->db('hosplan_unit')
          ->where('id_unit', $id_unit)
          ->toArray();
    
      return $this->draw('editunit.html', [
          'unit' => $unit[0],
      ]);
    }

    public function postUpdateUnit()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_unit'])) {
          redirect(url([ADMIN, 'hosplan', 'unit']));  
          return;
      }

      $id_unit = $_POST['id_unit'];

      $cek = $this->db('hosplan_unit')->where('id_unit', $id_unit)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'unit']));
          return;
      }

      $this->db('hosplan_unit')
          ->where('id_unit', $id_unit)
          ->update([
              'id_unit' => $id_unit,
              'nama_unit' => $_POST['nama_unit'],
              'jabatan' => $_POST['jabatan'],
              'nama_pegawai' => $_POST['nama_pegawai'],
              'nip' => $_POST['nip'],
          ]);

      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'unit'], ['unit' => $unit]));
    }

    public function getDeleteUnit($id_unit)
    {
      $cek = $this->db('hosplan_unit')->where('id_unit', $id_unit)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'unit']));
          return;
      }
      $this->db('hosplan_unit')->where('id_unit', $id_unit)->delete();
    
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      redirect(url([ADMIN, 'hosplan', 'unit'], ['unit' => $unit]));
    }

    public function getPptk()
    {
      $this->_addHeaderFiles();

      $tabel_pptk = $this->db('hosplan_pptk')
            ->toArray();

      return $this->draw('pptk.html', ['tabel_pptk' => $tabel_pptk]);
    }

    public function getTambahPptk()
    {
      $this->_addHeaderFiles();

      $pptk = $this->db('hosplan_pptk')
            ->toArray();

      return $this->draw('tambahpptk.html', ['pptk' => $pptk]);
    }

    public function postSavePptk()
    {
      $this->db('hosplan_pptk')->save([
        'nama_pptk' => $_POST['nama_pptk'],
        'nip' => $_POST['nip'],
      ]);

      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'pptk'], ['pptk' => $pptk]));
    }
    
    public function getEditPptk($id_pptk)
    {
      $this->_addHeaderFiles();
    
      $pptk = $this->db('hosplan_pptk')
          ->where('id_pptk', $id_pptk)
          ->toArray();
    
      return $this->draw('editpptk.html', [
          'pptk' => $pptk[0],
      ]);
    }

    public function postUpdatePptk()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_pptk'])) {
          redirect(url([ADMIN, 'hosplan', 'pptk']));  
          return;
      }

      $id_pptk = $_POST['id_pptk'];

      $cek = $this->db('hosplan_pptk')->where('id_pptk', $id_pptk)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'pptk']));
          return;
      }

      $this->db('hosplan_pptk')
          ->where('id_pptk', $id_pptk)
          ->update([
              'id_pptk' => $id_pptk,
              'nama_pptk' => $_POST['nama_pptk'],
              'nip' => $_POST['nip'],
          ]);

      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'pptk'], ['pptk' => $pptk]));
    }

    public function getDeletePptk($id_pptk)
    {
      $cek = $this->db('hosplan_pptk')->where('id_pptk', $id_pptk)->oneArray();
  
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'pptk']));
          return;
      }
      $this->db('hosplan_pptk')->where('id_pptk', $id_pptk)->delete();
    
      $pptk = isset($_GET['pptk']) ? $_GET['pptk'] : '';
      redirect(url([ADMIN, 'hosplan', 'pptk'], ['pptk' => $pptk]));
    }

    public function getKonfirmasiEdit()
    {
      $this->_addHeaderFiles();

      // Hitung jumlah upload total dan jumlah status Diajukan
      $rkaRows = $this->db('hosplan_uploadrka')->toArray();
      $rbaRows = $this->db('hosplan_uploadrba')->toArray();
      $bukbelRows = $this->db('hosplan_uploadbukbel')->toArray();

      $jumlahUploadRKA = is_array($rkaRows) ? count($rkaRows) : 0;
      $jumlahUploadRBA = is_array($rbaRows) ? count($rbaRows) : 0;
      $jumlahUploadBukbel = is_array($bukbelRows) ? count($bukbelRows) : 0;

      $pendingRKA = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();
      $pendingRBA = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();
      $pendingBukbel = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->where('hosplan_status.status_surat', 'Diajukan')
        ->toArray();

      $sub_modules = [
        ['name' => 'Konfirmasi Usulan RKA', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanrkaedit']), 'icon' => 'clipboard', 'desc' => 'Konfirmasi Data Usulan RKA', 'badge' => (is_array($pendingRKA) ? count($pendingRKA) : 0)],
        ['name' => 'Konfirmasi Usulan RBA', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanrbasedit']), 'icon' => 'clipboard', 'desc' => 'Konfirmasi Data Usulan RBA', 'badge' => (is_array($pendingRBA) ? count($pendingRBA) : 0)],
        ['name' => 'Konfirmasi Usulan BukBel', 'url' => url([ADMIN, 'hosplan', 'konfirmasiusulanbukbeledit']), 'icon' => 'book', 'desc' => 'Konfirmasi Data Usulan Buku Belanja', 'badge' => (is_array($pendingBukbel) ? count($pendingBukbel) : 0)],
      ];

      return $this->draw('konfirmasiedit.html', [
        'sub_modules' => $sub_modules,
        'jumlahUploadRKA' => $jumlahUploadRKA,
        'jumlahUploadRBA' => $jumlahUploadRBA,
        'jumlahUploadBukbel' => $jumlahUploadBukbel,
        'pendingRKA' => (is_array($pendingRKA) ? count($pendingRKA) : 0),
        'pendingRBA' => (is_array($pendingRBA) ? count($pendingRBA) : 0),
        'pendingBukbel' => (is_array($pendingBukbel) ? count($pendingBukbel) : 0),
      ]);
    }

    public function getKonfirmasiUsulanRKAEdit()
    {
      $this->_addHeaderFiles();

      $surat = $this->db('hosplan_surat')->toArray();
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedUnitRaw = isset($_GET['id_unit']) ? $_GET['id_unit'] : 'all';
      $selectedUnit = is_numeric($selectedUnitRaw) ? (int) $selectedUnitRaw : $selectedUnitRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrka')
        ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat=hosplan_uploadrka.id_surat')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
        ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit=hosplan_uploadrka.id_unit');

      if ($selectedUnit !== 'all' && is_int($selectedUnit)) {
        $query->where('hosplan_uploadrka.id_unit', $selectedUnit);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanrka = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrka/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrka)) {
        foreach ($tabel_uploadusulanrka as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRKA = is_array($tabel_uploadusulanrka) ? count($tabel_uploadusulanrka) : 0;
      $totalKonfirmasiRKA = $totalUploadRKA;
      $isUnitSelected = ($selectedUnit !== 'all' && is_int($selectedUnit) && $selectedUnit > 0);

      return $this->draw('konfirmasiusulanrkaedit.html', [
        'tabel_uploadusulanrka' => $tabel_uploadusulanrka,
        'surat' => $surat,
        'jenis' => $jenis,
        'status' => $status,
        'unit' => $unit,
        'selectedUnit' => $selectedUnit,
        'totalUploadRKA' => $totalUploadRKA,
        'totalKonfirmasiRKA' => $totalKonfirmasiRKA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getKonfirmasiStatusRKAEdit($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $surat = $this->db('hosplan_surat')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $unit = $this->db('hosplan_unit')->toArray();
    
      $uploadrka = $this->db('hosplan_uploadrka')
          ->leftJoin('hosplan_surat', 'hosplan_surat.id_surat = hosplan_uploadrka.id_surat')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrka.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrka.id_status_surat')
          ->leftJoin('hosplan_unit', 'hosplan_unit.id_unit = hosplan_uploadrka.id_unit')
          ->where('hosplan_uploadrka.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status
      if ($uploadrka && isset($uploadrka[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanrka/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanrka/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadrka[0]['file_usulan']) ? $uploadrka[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadrka[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadrka[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadrka[0]['file_url'] = '';
          }
        } else {
          $uploadrka[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusrkaedit.html', [
          'uploadrka' => $uploadrka[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'surat'  => $surat,
          'unit'  => $unit,

      ]);
    }

    public function postUpdateStatusRKAEdit()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrka')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrka']));
          return;
      }

      $this->db('hosplan_uploadrka')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $surat = isset($_GET['surat']) ? $_GET['surat'] : '';
      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanrka'], ['surat' => $surat, 'jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getKonfirmasiUsulanRBAEdit()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadrba')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadrba.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadrba.id_pptk', $selectedPptk);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanrba = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanrba/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanrba)) {
        foreach ($tabel_uploadusulanrba as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadRBA = is_array($tabel_uploadusulanrba) ? count($tabel_uploadusulanrba) : 0;
      $totalKonfirmasiRBA = $totalUploadRBA;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('konfirmasiusulanrbaedit.html', [
        'tabel_uploadusulanrba' => $tabel_uploadusulanrba,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadRBA' => $totalUploadRBA,
        'totalKonfirmasiRBA' => $totalKonfirmasiRBA,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }  

    public function getKonfirmasiStatusRBAEdit($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();
    
      $uploadrba = $this->db('hosplan_uploadrba')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadrba.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadrba.id_status_surat')
          ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk = hosplan_uploadrba.id_pptk')
          ->where('hosplan_uploadrba.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status RBA
      if ($uploadrba && isset($uploadrba[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanrba/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanrba/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadrba[0]['file_usulan']) ? $uploadrba[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadrba[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadrba[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadrba[0]['file_url'] = '';
          }
        } else {
          $uploadrba[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusrbaedit.html', [
          'uploadrba' => $uploadrba[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'pptk'  => $pptk,

      ]);
    }

    public function postUpdateStatusRBAEdit()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadrba')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanrba']));
          return;
      }

      $this->db('hosplan_uploadrba')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanrbaedit'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    public function getKonfirmasiUsulanBukbelEdit()
    {
      $this->_addHeaderFiles();

      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();

      // Filter Unit: dukung "Semua Unit" (all) sebagai default
      $selectedPptkRaw = isset($_GET['id_pptk']) ? $_GET['id_pptk'] : 'all';
      $selectedPptk = is_numeric($selectedPptkRaw) ? (int) $selectedPptkRaw : $selectedPptkRaw; // bisa integer atau 'all'

      $query = $this->db('hosplan_uploadbukbel')
        ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
        ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
        ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk=hosplan_uploadbukbel.id_pptk');

      if ($selectedPptk !== 'all' && is_int($selectedPptk)) {
        $query->where('hosplan_uploadbukbel.id_pptk', $selectedPptk);
      }
      // Hanya tampilkan status "Diajukan" pada halaman konfirmasi
      $query->where('hosplan_status.status_surat', 'Diajukan');

      $tabel_uploadusulanbukbel = $query->toArray();
      // Bangun URL file lampiran dengan fallback lokasi lama
      $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
      $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
      $legacyFs = __DIR__ . '/../../uploads/surat/';
      $legacyWeb = '/uploads/surat/';
      if (is_array($tabel_uploadusulanbukbel)) {
        foreach ($tabel_uploadusulanbukbel as &$row) {
          $fn = isset($row['file_usulan']) ? $row['file_usulan'] : '';
          if ($fn) {
            if (is_file($pluginFs . $fn)) {
              $row['file_url'] = $pluginWeb . $fn;
            } elseif (is_file($legacyFs . $fn)) {
              $row['file_url'] = $legacyWeb . $fn;
            } else {
              $row['file_url'] = '';
            }
          } else {
            $row['file_url'] = '';
          }
        }
        unset($row);
      }
      $totalUploadBukbel = is_array($tabel_uploadusulanbukbel) ? count($tabel_uploadusulanbukbel) : 0;
      $totalKonfirmasiBukbel = $totalUploadBukbel;
      $isUnitSelected = ($selectedPptk !== 'all' && is_int($selectedPptk) && $selectedPptk > 0);

      return $this->draw('konfirmasiusulanbukbel.html', [
        'tabel_uploadusulanbukbel' => $tabel_uploadusulanbukbel,
        'jenis' => $jenis,
        'status' => $status,
        'pptk' => $pptk,
        'selectedPptk' => $selectedPptk,
        'totalUploadBukbel' => $totalUploadBukbel,
        'totalKonfirmasiBukbel' => $totalKonfirmasiBukbel,
        'isUnitSelected' => $isUnitSelected,
      ]);
    }

    public function getKonfirmasiStatusBukbelEdit($id_usulan)
    {
      $this->_addHeaderFiles();
      
      $jenis = $this->db('hosplan_jenis')->toArray();
      $status = $this->db('hosplan_status')->toArray();
      $pptk = $this->db('hosplan_pptk')->toArray();
    
      $uploadbukbel = $this->db('hosplan_uploadbukbel')
          ->leftJoin('hosplan_jenis', 'hosplan_jenis.id_jenis_usulan=hosplan_uploadbukbel.id_jenis_usulan')
          ->leftJoin('hosplan_status', 'hosplan_status.id_status_surat=hosplan_uploadbukbel.id_status_surat')
          ->leftJoin('hosplan_pptk', 'hosplan_pptk.id_pptk = hosplan_uploadbukbel.id_pptk')
          ->where('hosplan_uploadbukbel.id_usulan', $id_usulan)
          ->toArray();

      // Bangun URL file lampiran untuk pratinjau pada halaman konfirmasi status Bukbel
      if ($uploadbukbel && isset($uploadbukbel[0])) {
        $pluginFs = __DIR__ . '/uploads/usulanbukbel/';
        $pluginWeb = '/plugins/hosplan/uploads/usulanbukbel/';
        $legacyFs = __DIR__ . '/../../uploads/surat/';
        $legacyWeb = '/uploads/surat/';
        $fn = isset($uploadbukbel[0]['file_usulan']) ? $uploadbukbel[0]['file_usulan'] : '';
        if ($fn) {
          if (is_file($pluginFs . $fn)) {
            $uploadbukbel[0]['file_url'] = $pluginWeb . $fn;
          } elseif (is_file($legacyFs . $fn)) {
            $uploadbukbel[0]['file_url'] = $legacyWeb . $fn;
          } else {
            $uploadbukbel[0]['file_url'] = '';
          }
        } else {
          $uploadbukbel[0]['file_url'] = '';
        }
      }
    
      return $this->draw('statusbukbeledit.html', [
          'uploadbukbel' => $uploadbukbel[0],
          'status'  => $status,
          'jenis'  => $jenis,
          'pptk'  => $pptk,
      ]);
    }

    public function postUpdateStatusBukbelEdit()
    {
      // Pastikan data dikirim via POST
      if (!isset($_POST['id_usulan'])) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbeledit']));  
          return;
      }

      $id_usulan = $_POST['id_usulan'];

      $cek = $this->db('hosplan_uploadbukbel')->where('id_usulan', $id_usulan)->oneArray();       
      if (!$cek) {
          redirect(url([ADMIN, 'hosplan', 'uploadusulanbukbeledit']));
          return;
      }

      $this->db('hosplan_uploadbukbel')
          ->where('id_usulan', $id_usulan)
          ->update([
              'id_usulan' => $id_usulan,
              'id_status_surat' => $_POST['id_status_surat'],
              'keterangan' => $_POST['keterangan'],
          ]);

      $jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
      $status = isset($_GET['status']) ? $_GET['status'] : '';
      $unit = isset($_GET['unit']) ? $_GET['unit'] : '';
      $_SESSION['flashdata']['added'] = true;
      redirect(url([ADMIN, 'hosplan', 'konfirmasiusulanbukbel'], ['jenis' => $jenis, 'status' => $status, 'unit' => $unit, 'edited' => 1]));
    }

    private function _addHeaderFiles()
    {
        $this->core->addCSS(url('assets/css/dataTables.bootstrap.min.css'));
        $this->core->addJS(url('assets/jscripts/jquery.dataTables.min.js'));
        $this->core->addJS(url('assets/jscripts/dataTables.bootstrap.min.js'));
        $this->core->addCSS(url('assets/css/bootstrap-datetimepicker.css'));
        $this->core->addJS(url('assets/jscripts/moment-with-locales.js'));
        $this->core->addJS(url('assets/jscripts/bootstrap-datetimepicker.js'));
    }  
}
