<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/controllers/PenjualanController.php';

$controller = new PenjualanController();
$controller->handleRequest();
