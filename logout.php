<?php
require_once 'includes/auth.php';

session_destroy();
redirect('index.php'); 