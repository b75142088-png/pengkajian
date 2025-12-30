CREATE DATABASE IF NOT EXISTS rsud_sanjiwani;
USE rsud_sanjiwani;

CREATE TABLE IF NOT EXISTS asesmen_pra_anestesi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- Identitas Header
    no_rm VARCHAR(50),
    nama_pasien VARCHAR(100),
    tgl_lahir VARCHAR(50),
    jk_header ENUM('L','P'),
    regno VARCHAR(50),
    ruangan VARCHAR(100),
    tgl_asesmen DATE,
    jam_asesmen TIME,
    
    -- Bagian I: Penata Anestesi
    umur INT,
    jk_penata ENUM('L','P'),
    menikah ENUM('Y','T'),
    pekerjaan VARCHAR(100),
    
    -- Kebiasaan
    rokok ENUM('Y','T'),
    rokok_jumlah VARCHAR(50),
    kopi ENUM('Y','T'),
    kopi_jumlah VARCHAR(50),
    alkohol ENUM('Y','T'),
    alkohol_jumlah VARCHAR(50),
    olahraga ENUM('Y','T'),
    olahraga_jumlah VARCHAR(50),
    
    -- Pengobatan
    obat_resep VARCHAR(10), -- Checkbox value
    obat_bebas VARCHAR(10), -- Checkbox value
    obat_bebas_ket TEXT,
    aspirin ENUM('Y','T'),
    aspirin_dosis VARCHAR(100),
    painkiller ENUM('Y','T'),
    painkiller_dosis VARCHAR(100),
    steroid ENUM('Y','T'),
    steroid_ket VARCHAR(100),
    alergi_obat ENUM('Y','T'),
    alergi_obat_ket VARCHAR(100),
    
    -- Alergi Lain
    alergi_lateks ENUM('Y','T'),
    alergi_plester ENUM('Y','T'),
    alergi_makanan ENUM('Y','T'),
    
    -- Riwayat Keluarga (Kiri)
    rk_perdarahan_abnormal ENUM('Y','T'),
    rk_pembekuan_abnormal ENUM('Y','T'),
    rk_masalah_pembiusan ENUM('Y','T'),
    rk_jantung_koroner ENUM('Y','T'),
    rk_diabetes ENUM('Y','T'),
    
    -- Riwayat Keluarga (Kanan)
    rk_serangan_jantung ENUM('Y','T'),
    rk_hipertensi ENUM('Y','T'),
    rk_tbc ENUM('Y','T'),
    rk_penyakit_berat_lain ENUM('Y','T'),
    
    rk_penjelasan_ya TEXT,
    
    -- Komunikasi
    bahasa_indo VARCHAR(10),
    bahasa_lain VARCHAR(10),
    bahasa_lain_ket VARCHAR(100),
    kom_mata ENUM('Y','T'),
    kom_telinga ENUM('Y','T'),
    kom_bicara ENUM('Y','T'),
    
    -- Riwayat Pasien (Kiri)
    rp_perdarahan_abnormal ENUM('Y','T'),
    rp_pembekuan_abnormal ENUM('Y','T'),
    rp_maag ENUM('Y','T'),
    rp_anemia ENUM('Y','T'),
    rp_sesak ENUM('Y','T'),
    rp_asma ENUM('Y','T'),
    rp_pingsan ENUM('Y','T'),
    
    -- Riwayat Pasien (Kanan)
    rp_nyeri_dada ENUM('Y','T'),
    rp_hepatitis ENUM('Y','T'),
    rp_hipertensi ENUM('Y','T'),
    rp_ngorok ENUM('Y','T'),
    rp_penyakit_berat_lain ENUM('Y','T'),
    rp_diabetes ENUM('Y','T'),
    
    rp_penjelasan_ya TEXT,
    
    -- Riwayat Tambahan
    transfusi ENUM('Y','T'),
    transfusi_tahun VARCHAR(20),
    hiv_check ENUM('Y','T'),
    hiv_tahun VARCHAR(20),
    hiv_res VARCHAR(20),
    
    -- Alat Bantu
    lensa_kontak ENUM('Y','T'),
    kacamata ENUM('Y','T'),
    alat_bantu_dengar ENUM('Y','T'),
    gigi_palsu ENUM('Y','T'),
    
    -- Operasi & Kesehatan
    op_lokal_ket VARCHAR(255),
    op_regional_ket VARCHAR(255),
    op_umum_ket VARCHAR(255),
    
    terakhir_periksa_tgl DATE,
    terakhir_periksa_tempat VARCHAR(100),
    terakhir_periksa_penyakit VARCHAR(255),
    
    -- Pasien Perempuan
    jml_hamil VARCHAR(10),
    jml_anak VARCHAR(10),
    menstruasi VARCHAR(50),
    menyusui ENUM('Y','T'),
    
    -- Bagian II: Dokter Anestesi
    anamnesis TEXT,
    
    -- Kajian Sistem Dokter (Kiri)
    dok_hilang_gigi ENUM('Y','T'),
    dok_masalah_leher ENUM('Y','T'),
    dok_leher_pendek ENUM('Y','T'),
    dok_batuk ENUM('Y','T'),
    dok_sesak ENUM('Y','T'),
    dok_infeksi_nafas ENUM('Y','T'),
    dok_mens_abnormal ENUM('Y','T'),
    dok_stroke ENUM('Y','T'),
    
    -- Kajian Sistem Dokter (Kanan)
    dok_sakit_dada ENUM('Y','T'),
    dok_jantung_abnormal ENUM('Y','T'),
    dok_muntah ENUM('Y','T'),
    dok_susah_kencing ENUM('Y','T'),
    dok_kejang ENUM('Y','T'),
    dok_hamil ENUM('Y','T'),
    dok_pingsan ENUM('Y','T'),
    dok_obesitas ENUM('Y','T'),
    
    dok_keterangan TEXT,
    
    -- Keadaan Umum
    ku_kesadaran VARCHAR(50),
    ku_visus VARCHAR(50),
    ku_faring VARCHAR(50),
    ku_gigi_palsu VARCHAR(50),
    ku_keterangan TEXT,
    
    -- Fisik
    fisik_tinggi VARCHAR(10),
    fisik_berat VARCHAR(10),
    fisik_td VARCHAR(20),
    fisik_nadi VARCHAR(20),
    fisik_rr VARCHAR(20),
    fisik_suhu VARCHAR(10),
    
    fisik_paru VARCHAR(255),
    fisik_jantung VARCHAR(255),
    fisik_abdomen VARCHAR(255),
    fisik_ekstrimitas VARCHAR(255),
    fisik_neurologi VARCHAR(255),
    fisik_lain VARCHAR(255),
    
    -- Laboratorium
    lab_hb_ht VARCHAR(50),
    lab_pt_aptt VARCHAR(50),
    lab_kehamilan VARCHAR(50),
    lab_kalium VARCHAR(50),
    lab_ureum VARCHAR(50),
    lab_keterangan VARCHAR(255),
    
    lab_rontgen VARCHAR(50),
    lab_ekg VARCHAR(50),
    lab_nacl VARCHAR(50),
    lab_co2 VARCHAR(50),
    lab_lain VARCHAR(50),
    
    -- Diagnosa
    masalah TEXT,
    asa VARCHAR(20),
    saran TEXT,
    
    -- Rencana Anestesi
    ane_umum VARCHAR(10),
    au_iv VARCHAR(10),
    au_sm VARCHAR(10),
    au_lma VARCHAR(10),
    au_ett VARCHAR(10),
    
    ane_reg VARCHAR(10),
    ar_sab VARCHAR(10),
    ar_epi VARCHAR(10),
    ar_cse VARCHAR(10),
    ar_pnb VARCHAR(10),
    
    ane_umum_reg VARCHAR(10),
    
    -- Puasa
    puasa_jam TIME,
    puasa_tgl DATE,
    
    -- TTD
    signature_image LONGTEXT,
    nama_dokter_ttd VARCHAR(100)
);