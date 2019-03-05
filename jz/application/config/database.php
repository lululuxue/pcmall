<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;

$db['default'] = array(
	'dsn'	=> '',
	'hostname' => '120.79.138.161:3306',
	'username' => 'lixxmei',
	'password' => 'phplixxmei',
	'database' => 'clothes',
    //'hostname' => 'localhost:3306',
    //'username' => 'root',
    //'password' => 'root',
    //'database' => 'jz',
	'dbdriver' => 'mysqli',
	'dbprefix' => 'my_',
	'pconnect' => FALSE,
	'db_debug' => (ENVIRONMENT !== 'production'),
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8mb4',
	'dbcollat' => 'utf8mb4_general_ci',
	'swap_pre' => '',
	'encrypt' => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);