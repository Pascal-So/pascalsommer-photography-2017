-- the tables needed for the blog

CREATE TABLE `posts` (
	`id` int(11) NOT NULL auto_increment primary key,
	`title` varchar(255) NOT NULL,
	`slug` varchar(255) NOT NULL,
	`created` timestamp NOT NULL,
	UNIQUE KEY `slug` (`slug`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `photos` (
	`id` int(11) NOT NULL auto_increment primary key,
	`post_id` int(11) NOT NULL references posts (id),
	`path` varchar(1024) NOT NULL,
	`description` text,
	UNIQUE KEY `path` (`path`)
)  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `comments` (
	`id` int(11) NOT NULL auto_increment primary key,
	`photo_id` int(11) NOT NULL references photos (id),
	`name` varchar(255) NOT NULL,
	`comment` text NOT NULL,
	`created` timestamp NOT NULL
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `staging` (
	`id` int(11) NOT NULL auto_increment primary key,
	`ordering` int(11),
	`path` varchar(1024) NOT NULL,
	`description` text,
	`active` bool NOT NULL default 0
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- table needed for login system

CREATE TABLE `users` (
	`id` int(11) NOT NULL auto_increment primary key,
	`username` varchar(128) NOT NULL,
	`hash` varchar(512) NOT NULL,
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;