<?php
require_once __DIR__.'/funciones.php';
if(!isLoggedIn()) redirect('login.php');
if(hasRole('Admin')) redirect('admin/dashboard.php');
redirect('guardia/dashboard.php');
