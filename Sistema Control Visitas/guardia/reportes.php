<?php require_once __DIR__.'/../funciones.php'; requireLogin('..'); if(hasRole('Admin')) redirect('../admin/reportes.php'); redirect('dashboard.php');
