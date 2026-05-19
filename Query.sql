CREATE TABLE `posts` (
                         `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                         `title` varchar(255) NOT NULL,
                         `content` text NOT NULL,
                         `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB CHARSET=utf8;
