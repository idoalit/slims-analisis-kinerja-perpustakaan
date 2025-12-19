<?php
/**
 * Plugin Name: Analisis Kinerja Perpustakaan
 * Plugin URI: https://gitlab.com/hendrowicaksono/pedoman-analisis-kinerja-perpustakaan-indonesia/
 * Description: Plugin untuk menganalisis kinerja perpustakaan berdasarkan Pedoman Analisis Kinerja Perpustakaan Indonesia (PAKPI)
 * Version: 1.0.0
 * Author: Hendro Wicaksono
 * Author URI: https://gitlab.com/hendrowicaksono
 */

use SLiMS\Plugins;

// Register menu menggunakan pattern official
$plugin = Plugins::getInstance();
$plugin->registerMenu('reporting', 'Analisis Kinerja Perpustakaan', __DIR__ . '/index.php');
$plugin->registerMenu('reporting', 'Eksplorasi Analisis Kinerja Perpustakaan', __DIR__ . '/eksplorasi.php');
