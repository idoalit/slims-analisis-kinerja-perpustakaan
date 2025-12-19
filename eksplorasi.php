<?php
/**
 * Eksplorasi Analisis Kinerja Perpustakaan Indonesia (PAKPI)
 * Query eksplorasi untuk analisis mendalam per indikator
 */

// ===== SECURITY LAYER =====
if (!defined('INDEX_AUTH')) {
    define('INDEX_AUTH', '1');
    define('SIMBIO_DB_USER', $_SESSION['username'] ?? '');
    define('SIMBIO_DB_PASS', $_SESSION['password'] ?? '');
}

// Cek apakah dipanggil sebagai plugin atau standalone
if (!defined('SB')) {
    // Load SLiMS core jika belum ter-load
    require '../../../sysconfig.inc.php';
}

global $dbs, $sysconf;
require SB . 'admin/default/session.inc.php';
require SB . 'admin/default/session_check.inc.php';

// Check privileges
$can_read = utility::havePrivilege('reporting', 'r');
if (!$can_read) {
    die('<div class="errorBox">' . __('You do not have permission!') . '</div>');
}

// Detect if this is report view (iframe mode)
$reportView = isset($_GET['reportView']);

// ╔════════════════════════════════════════════════╗
// ║           PARENT MODE: Filter Form             ║
// ╚════════════════════════════════════════════════╝
if (!$reportView):
?>
<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Eksplorasi Analisis Kinerja Perpustakaan</title>
        <style>
            .eksplorasi-wrapper {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: transparent;
                margin: 0;
                padding: 0;
            }
            .form-container {
                background: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                margin-bottom: 10px;
            }
            .form-inline {
                display: flex;
                align-items: center;
                gap: 15px;
                flex-wrap: wrap;
            }
            .form-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .form-group label {
                font-weight: 500;
                color: #555;
                font-size: 14px;
            }
            .form-group input[type="text"],
            .form-group select {
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 14px;
                width: 120px;
            }
            .btn-submit {
                padding: 8px 20px;
                background: #4A90E2;
                color: white;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                transition: background 0.2s;
            }
            .btn-submit:hover {
                background: #357ABD;
            }
            .btn-info {
                padding: 6px 12px;
                background: #e0e0e0;
                color: #333;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-size: 13px;
                transition: background 0.2s;
            }
            .btn-info:hover {
                background: #d0d0d0;
            }
            #report-frame {
                width: 100%;
                height: 100vh;
                border: none;
                background: transparent;
                overflow: hidden;
                display: block;
            }
            .report-container {
                margin-top: 0;
                width: 100%;
            }
            .modal {
                display: none;
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                animation: fadeIn 0.3s;
            }
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            .modal-content {
                background: white;
                margin: 50px auto;
                padding: 30px;
                border-radius: 8px;
                max-width: 800px;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                animation: slideDown 0.3s;
            }
            @keyframes slideDown {
                from {
                    transform: translateY(-50px);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            .modal-close {
                float: right;
                font-size: 28px;
                font-weight: bold;
                color: #999;
                cursor: pointer;
                line-height: 20px;
            }
            .modal-close:hover {
                color: #333;
            }
            .modal h3, .modal h4 {
                color: #4A90E2;
            }
            .modal ul {
                line-height: 1.8;
            }
        </style>
    </head>
    <body>
        <div class="eksplorasi-wrapper">
        <div class="form-container">
            <form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>" target="report-frame" class="form-inline">
                <input type="hidden" name="mod" value="<?= $_GET['mod'] ?? '' ?>" />
                <input type="hidden" name="id" value="<?= $_GET['id'] ?? '' ?>" />
                <input type="hidden" name="sec" value="/" />
                <input type="hidden" name="reportView" value="1">
                <div class="form-group">
                    <label>Tahun:</label>
                    <input type="text" name="tahun" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Indikator:</label>
                    <select name="indikator" required>
                        <option value="b211">B.2.1.1 Perputaran Koleksi</option>
                        <option value="b212">B.2.1.2 Pinjaman Per Kapita</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit">📊 Tampilkan Data Eksplorasi</button>
                <button class="btn-info" onclick="document.getElementById('infoModal').style.display='block'">ℹ️ Info</button>
            </form>
        </div>

        <div class="report-container">
            <iframe name="report-frame" id="report-frame" 
                    src="<?php 
                        // Build initial iframe URL
                        $params = [
                            'mod' => $_GET['mod'] ?? '',
                            'id' => $_GET['id'] ?? '',
                            'sec' => '/',
                            'reportView' => '1',
                            'tahun' => date('Y'),
                            'indikator' => 'b211'
                        ];
                        echo $_SERVER['PHP_SELF'] . '?' . http_build_query($params);
                    ?>">
            </iframe>
        </div>
        
        <script>
            // Auto-resize iframe to fit content with buffer
            window.addEventListener('message', function(e) {
                if (e.data.type === 'resize') {
                    var iframe = document.getElementById('report-frame');
                    iframe.style.height = (e.data.height + 50) + 'px';
                }
            });
            
            // Initial resize
            setTimeout(function() {
                document.getElementById('report-frame').style.height = '2000px';
            }, 100);
        </script>

        <!-- Info Modal -->
        <div id="infoModal" class="modal" onclick="if(event.target==this) this.style.display='none'">
            <div class="modal-content">
                <span class="modal-close" onclick="document.getElementById('infoModal').style.display='none'">&times;</span>
                <h3 style="margin-top:0; color: #4A90E2;">🔍 Eksplorasi Analisis Kinerja Perpustakaan Indonesia (PAKPI)</h3>
                <p>Halaman ini menyediakan <strong>data eksplorasi mendalam</strong> untuk setiap indikator kinerja perpustakaan berdasarkan PAKPI 2021.</p>
                
                <h4 style="margin: 15px 0 10px 0; color: #4A90E2;">📊 Query Eksplorasi yang Tersedia</h4>
                <ul>
                    <li><strong>B.2.1.1 Perputaran Koleksi:</strong>
                        <ul>
                            <li>Buku Paling Banyak Dipinjam (Top 30)</li>
                            <li>Subyek Paling Banyak Dipinjam (Top 30)</li>
                        </ul>
                    </li>
                    <li><strong>B.2.1.2 Pinjaman Per Kapita:</strong>
                        <ul>
                            <li>Anggota Paling Banyak Meminjam (Top 30)</li>
                            <li>Jenis Keanggotaan Paling Banyak Meminjam (Top 30)</li>
                        </ul>
                    </li>
                </ul>

                <h4 style="margin: 15px 0 10px 0; color: #4A90E2;">📚 Referensi</h4>
                <ul>
                    <li><strong>PAKPI 2021:</strong> <a href="https://s.id/pakpi2021" target="_blank">Pedoman Analisis Kinerja Perpustakaan Indonesia</a></li>
                    <li><strong>SNI ISO 2789:2013:</strong> Standar perpustakaan yang mengatur tentang statistik perpustakaan</li>
                    <li><strong>Repository:</strong> <a href="https://gitlab.com/hendrowicaksono/pedoman-analisis-kinerja-perpustakaan-indonesia/" target="_blank">GitLab - PAKPI SQL Queries</a></li>
                </ul>
            </div>
        </div>
        </div>
    </body>
    </html>
    <?php
    exit; // IMPORTANT: Exit parent mode
endif;

// ╔════════════════════════════════════════════════╗
// ║         IFRAME MODE: Report Display            ║
// ╚════════════════════════════════════════════════╝

// Iframe mode: Show report
$tahun = isset($_GET['tahun']) ? $dbs->escape_string(trim($_GET['tahun'])) : date('Y');
$indikator = isset($_GET['indikator']) ? $_GET['indikator'] : 'b211';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksplorasi Data - <?php echo $tahun; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: transparent;
            padding: 0;
            color: #333;
            overflow-x: hidden;
        }
        .report-header {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .report-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .report-subtitle {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .btn-print {
            padding: 8px 16px;
            background: #4A90E2;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .btn-print:hover {
            background: #357ABD;
        }
        .btn-print:active {
            background: #2868A8;
        }
        .eksplorasi-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4A90E2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        th {
            background: #f9f9f9;
            color: #333;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e0e0e0;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
        }
        tr:hover {
            background: #f8fafc;
        }
        tr:last-child td {
            border-bottom: none;
        }
        td:first-child {
            font-weight: 600;
            color: #4A90E2;
        }
        .rank-badge {
            display: inline-block;
            background: #4A90E2;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            min-width: 30px;
            text-align: center;
        }
        .rank-badge.top3 {
            background: #f59e0b;
        }
        .error-box {
            background: #fee2e2;
            border: 2px solid #fca5a5;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 8px;
            color: #991b1b;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }
        .error-box::before {
            content: "⚠️";
            font-size: 20px;
        }
        @media print {
            .non-printable {
                display: none !important;
            }
            body {
                background: white;
                padding: 0;
            }
            .eksplorasi-section {
                box-shadow: none;
                page-break-inside: avoid;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>
    <div class="report-header non-printable">
        <div>
            <div class="report-title">🔍 Eksplorasi Data PAKPI</div>
            <div class="report-subtitle">Tahun <?php echo htmlspecialchars($tahun); ?></div>
        </div>
        <button class="btn-print non-printable" onclick="window.print()">
            🖨️ Cetak Halaman Ini
        </button>
    </div>

<?php

// ============================================
// B.2.1.1 PERPUTARAN KOLEKSI - EKSPLORASI
// ============================================
if ($indikator == 'b211') {
    echo '<div class="eksplorasi-section">';
    echo '<div class="section-title">📚 Buku Paling Banyak Dipinjam (Top 30)</div>';
    
    $query_buku = "SELECT 
        b.title,
        b.biblio_id,
        COUNT(1) AS TotalPeminjaman
    FROM loan AS l 
    INNER JOIN item AS i ON l.item_code=i.item_code
    INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id
    WHERE l.loan_date LIKE ?
    GROUP BY b.biblio_id
    ORDER BY TotalPeminjaman DESC
    LIMIT 30";
    
    $stmt_buku = $dbs->prepare($query_buku);
    $tahun_pattern = $tahun . '-%';
    $stmt_buku->bind_param('s', $tahun_pattern);
    $stmt_buku->execute();
    $result_buku = $stmt_buku->get_result();
    
    if ($result_buku->num_rows > 0) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Peringkat</th>';
        echo '<th>Judul Buku</th>';
        echo '<th>ID Biblio</th>';
        echo '<th>Total Peminjaman</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        $rank = 1;
        while ($row = $result_buku->fetch_assoc()) {
            echo '<tr>';
            $badge_class = $rank <= 3 ? 'rank-badge top3' : 'rank-badge';
            echo '<td><span class="' . $badge_class . '">#' . $rank . '</span></td>';
            echo '<td>' . htmlspecialchars($row['title']) . '</td>';
            echo '<td>' . htmlspecialchars($row['biblio_id']) . '</td>';
            echo '<td><strong>' . number_format($row['TotalPeminjaman']) . '</strong></td>';
            echo '</tr>';
            $rank++;
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="error-box">Tidak ada data buku untuk tahun ' . htmlspecialchars($tahun) . '</div>';
    }
    
    echo '</div>';
    
    // Subyek Paling Banyak Dipinjam
    echo '<div class="eksplorasi-section">';
    echo '<div class="section-title">📑 Subyek Paling Banyak Dipinjam (Top 30)</div>';
    
    $query_subyek = "SELECT 
        t.topic,
        t.topic_id,
        COUNT(1) AS TotalPeminjaman
    FROM mst_topic AS t 
    INNER JOIN biblio_topic AS bt ON t.topic_id=bt.topic_id
    INNER JOIN biblio AS b ON bt.biblio_id=b.biblio_id
    INNER JOIN item AS i ON b.biblio_id=i.biblio_id
    INNER JOIN loan AS l ON i.item_code=l.item_code
    WHERE l.loan_date LIKE ?
    GROUP BY t.topic_id
    ORDER BY TotalPeminjaman DESC
    LIMIT 30";
    
    $stmt_subyek = $dbs->prepare($query_subyek);
    $stmt_subyek->bind_param('s', $tahun_pattern);
    $stmt_subyek->execute();
    $result_subyek = $stmt_subyek->get_result();
    
    if ($result_subyek->num_rows > 0) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Peringkat</th>';
        echo '<th>Subyek</th>';
        echo '<th>ID Topik</th>';
        echo '<th>Total Peminjaman</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        $rank = 1;
        while ($row = $result_subyek->fetch_assoc()) {
            echo '<tr>';
            $badge_class = $rank <= 3 ? 'rank-badge top3' : 'rank-badge';
            echo '<td><span class="' . $badge_class . '">#' . $rank . '</span></td>';
            echo '<td>' . htmlspecialchars($row['topic']) . '</td>';
            echo '<td>' . htmlspecialchars($row['topic_id']) . '</td>';
            echo '<td><strong>' . number_format($row['TotalPeminjaman']) . '</strong></td>';
            echo '</tr>';
            $rank++;
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="error-box">Tidak ada data subyek untuk tahun ' . htmlspecialchars($tahun) . '</div>';
    }
    
    echo '</div>';
}

// ============================================
// B.2.1.2 PINJAMAN PER KAPITA - EKSPLORASI
// ============================================
if ($indikator == 'b212') {
    echo '<div class="eksplorasi-section">';
    echo '<div class="section-title">👥 Anggota Paling Banyak Meminjam (Top 30)</div>';
    
    $query_anggota = "SELECT 
        m.member_id,
        m.member_name,
        mt.member_type_name,
        COUNT(1) AS TotalPeminjaman
    FROM member AS m 
    INNER JOIN loan AS l ON m.member_id=l.member_id
    INNER JOIN item AS i ON l.item_code=i.item_code
    INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id
    INNER JOIN mst_member_type AS mt ON m.member_type_id=mt.member_type_id
    WHERE l.loan_date LIKE ?
    GROUP BY m.member_id
    ORDER BY TotalPeminjaman DESC
    LIMIT 30";
    
    $stmt_anggota = $dbs->prepare($query_anggota);
    $tahun_pattern = $tahun . '-%';
    $stmt_anggota->bind_param('s', $tahun_pattern);
    $stmt_anggota->execute();
    $result_anggota = $stmt_anggota->get_result();
    
    if ($result_anggota->num_rows > 0) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Peringkat</th>';
        echo '<th>ID Anggota</th>';
        echo '<th>Nama Anggota</th>';
        echo '<th>Jenis Keanggotaan</th>';
        echo '<th>Total Peminjaman</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        $rank = 1;
        while ($row = $result_anggota->fetch_assoc()) {
            echo '<tr>';
            $badge_class = $rank <= 3 ? 'rank-badge top3' : 'rank-badge';
            echo '<td><span class="' . $badge_class . '">#' . $rank . '</span></td>';
            echo '<td>' . htmlspecialchars($row['member_id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['member_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['member_type_name']) . '</td>';
            echo '<td><strong>' . number_format($row['TotalPeminjaman']) . '</strong></td>';
            echo '</tr>';
            $rank++;
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="error-box">Tidak ada data anggota untuk tahun ' . htmlspecialchars($tahun) . '</div>';
    }
    
    echo '</div>';
    
    // Jenis Keanggotaan Paling Banyak Meminjam
    echo '<div class="eksplorasi-section">';
    echo '<div class="section-title">🏷️ Jenis Keanggotaan Paling Banyak Meminjam (Top 30)</div>';
    
    $query_jenis = "SELECT 
        mt.member_type_name,
        mt.member_type_id,
        COUNT(1) AS TotalPeminjaman
    FROM mst_member_type AS mt
    INNER JOIN member AS m ON mt.member_type_id=m.member_type_id
    INNER JOIN loan AS l ON m.member_id=l.member_id
    INNER JOIN item AS i ON l.item_code=i.item_code
    INNER JOIN biblio AS b ON i.biblio_id=b.biblio_id
    WHERE l.loan_date LIKE ?
    GROUP BY mt.member_type_id
    ORDER BY TotalPeminjaman DESC
    LIMIT 30";
    
    $stmt_jenis = $dbs->prepare($query_jenis);
    $stmt_jenis->bind_param('s', $tahun_pattern);
    $stmt_jenis->execute();
    $result_jenis = $stmt_jenis->get_result();
    
    if ($result_jenis->num_rows > 0) {
        echo '<table>';
        echo '<thead><tr>';
        echo '<th>Peringkat</th>';
        echo '<th>Jenis Keanggotaan</th>';
        echo '<th>ID Tipe</th>';
        echo '<th>Total Peminjaman</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        $rank = 1;
        while ($row = $result_jenis->fetch_assoc()) {
            echo '<tr>';
            $badge_class = $rank <= 3 ? 'rank-badge top3' : 'rank-badge';
            echo '<td><span class="' . $badge_class . '">#' . $rank . '</span></td>';
            echo '<td>' . htmlspecialchars($row['member_type_name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['member_type_id']) . '</td>';
            echo '<td><strong>' . number_format($row['TotalPeminjaman']) . '</strong></td>';
            echo '</tr>';
            $rank++;
        }
        
        echo '</tbody></table>';
    } else {
        echo '<div class="error-box">Tidak ada data jenis keanggotaan untuk tahun ' . htmlspecialchars($tahun) . '</div>';
    }
    
    echo '</div>';
}

?>

<script>
// Auto-resize iframe
function resizeIframe() {
    var height = document.body.scrollHeight;
    window.parent.postMessage({
        type: 'resize',
        height: height
    }, '*');
}

// Resize on load and when content changes
window.addEventListener('load', resizeIframe);
setTimeout(resizeIframe, 500);
setTimeout(resizeIframe, 1000);
</script>

</body>
</html>
