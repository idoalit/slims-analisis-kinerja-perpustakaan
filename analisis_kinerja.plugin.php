<?php
/**
 * Plugin Name: Analisis Kinerja Perpustakaan
 * Plugin URI: https://github.com/adeism/slims-analisis-kinerja-perpustakaan
 * Description: Plugin untuk menganalisis kinerja perpustakaan berdasarkan Pedoman Analisis Kinerja Perpustakaan Indonesia (GitLab Hendro Wicaksono: https://gitlab.com/hendrowicaksono/pedoman-analisis-kinerja-perpustakaan-indonesia/) 
 * Version: 1.0.0
 * Author: Ade Ismail Siregar (adeismailbox@gmail.com)
 * Author URI: https://github.com/adeism
 */

use SLiMS\Plugins;

// Register menu menggunakan pattern official
$plugin = Plugins::getInstance();
$plugin->registerMenu('reporting', 'Analisis Kinerja Perpustakaan', __DIR__ . '/index.php');
$plugin->registerMenu('reporting', 'Eksplorasi Analisis Kinerja Perpustakaan', __DIR__ . '/eksplorasi.php');
