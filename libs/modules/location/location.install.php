<?php

function location_schema() {
    $schema = array();
    $schema['location'] = "CREATE TABLE `location` (
                              `id` varchar(255) NOT NULL DEFAULT '',
                              `name` varchar(255) DEFAULT NULL,
                              `data` text,
                              `location` point NOT NULL,
                              `updated` int(11) DEFAULT NULL,
                              PRIMARY KEY (`id`),
                              SPATIAL KEY `sx_location_location` (`location`)
                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    return $schema;
}