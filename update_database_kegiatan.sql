ALTER TABLE `kegiatan` 
ADD COLUMN `nomor_wa` varchar(20) DEFAULT NULL AFTER `penanggung_jawab`,
ADD COLUMN `status_reminder` tinyint(1) NOT NULL DEFAULT 0 AFTER `nomor_wa`;
