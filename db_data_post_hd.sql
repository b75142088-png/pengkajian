CREATE TABLE `db_data_post_hd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `on_hd_id` int(11) NOT NULL,
  `keluhan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keadaan_umum` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tekanan_darah` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nadi_post` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `respirasi` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bb_post` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lama_hd` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uf_removed` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sisa_priming` int(11) DEFAULT 0,
  `reuse` tinyint(1) DEFAULT 0,
  `tidak_reuse` tinyint(1) DEFAULT 0,
  `transfusi` int(11) DEFAULT 0,
  `beku_bocor1` tinyint(1) DEFAULT 0,
  `wash_out` int(11) DEFAULT 0,
  `single_use` tinyint(1) DEFAULT 0,
  `minum` int(11) DEFAULT 0,
  `pakai_8x` tinyint(1) DEFAULT 0,
  `jumlah` int(11) DEFAULT 0,
  `penyakit_menular` tinyint(1) DEFAULT 0,

  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),

  PRIMARY KEY (`id`),
  KEY `idx_on_hd_id` (`on_hd_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
