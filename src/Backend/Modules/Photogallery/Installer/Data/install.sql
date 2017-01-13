-- Create syntax for TABLE 'photogallery_album_content'
CREATE TABLE `photogallery_album_content` (
  `album_id` bigint(20) NOT NULL,
  `language` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `seo_url_overwrite` enum('N','Y') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description_overwrite` enum('N','Y') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seo_title_overwrite` enum('N','Y') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slideshow_extra_id` int(11) DEFAULT NULL,
  `lightbox_extra_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- Create syntax for TABLE 'photogallery_album_images'
CREATE TABLE `photogallery_album_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `sequence` int(11) NOT NULL,
  `created_on` datetime NOT NULL,
  `edited_on` datetime NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci,
  `hidden` enum('N','Y') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- Create syntax for TABLE 'photogallery_photogallery_album_images_content'
CREATE TABLE `photogallery_photogallery_album_images_content` (
  `image_id` bigint(20) NOT NULL,
  `language` varchar(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;

-- Create syntax for TABLE 'photogallery'
CREATE TABLE `photogallery` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hidden` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
`slideshow` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
`lightbox` enum('N','Y') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N',
  `created_on` timestamp NULL DEFAULT NULL,
  `edited_on` timestamp NULL DEFAULT NULL,
  `sequence` int(11) DEFAULT NULL,
  `status` enum('active','draft') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `publish_on` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=1 ;
