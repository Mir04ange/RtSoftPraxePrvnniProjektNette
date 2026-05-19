create table `posts`
(
    `id` int not null
);
CREATE TABLE `comments` (
	`id` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`post_id` int(11) NOT NULL,
	`name` varchar(250) NOT NULL,
	`email` varchar(250) NOT NULL,
	`content` text NOT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`)
) ENGINE=InnoDB CHARSET=utf8;
