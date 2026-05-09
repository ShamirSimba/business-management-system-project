<?php
/**
 * BMS Setup Verification Script
 * 
 * Verifies that all requirements are met before running BMS
 * Access this file at: http://localhost/BMS/verify.php
 */

require_once 'config/constants.php';

$checks = [
    'PHP Version' => [
        'required' => '7.4.0',
        'actual' => PHP_VERSION,
        'pass' => version_compare(PHP_VERSION, '7.4.0', '>=')
    ],
    'MySQL Support' => [
        'required' => 'mysqli extension',
        'actual' => extension_loaded('mysqli') ? 'Enabled' : 'Disabled',
        'pass' => extension_loaded('mysqli')
    ],
    'JSON Support' => [
        'required' => 'json extension',
        'actual' => extension_loaded('json') ? 'Enabled' : 'Disabled',
        'pass' => extension_loaded('json')
    ]
];

// Try database connection
$db_check = false;
$db_message = 'Connection failed';
try {
    $conn = @mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn) {
        $db_check = true;
        $db_message = 'Connected successfully';
        mysqli_close($conn);
    }
} catch (Exception $e) {
    $db_message = $e->getMessage();
}

$checks['Database Connection'] = [
    'required' => 'MySQL ' . DB_NAME . ' @ ' . DB_HOST,
    'actual' => $db_message,
    'pass' => $db_check
];

// Check file structure
$folders = ['config', 'classes', 'handlers', 'auth', 'api', 'modules', 'assets', 'database'];
foreach ($folders as $folder) {
    $path = __DIR__ . '/' . $folder;
    $checks["Folder: $folder"] = [
        'required' => 'Directory exists',
        'actual' => is_dir($path) ? 'Found' : 'Missing',
        'pass' => is_dir($path)
    ];
}

// Check critical files
$files = [
    'composer.json' => 'Composer dependencies',
    'database/seed.php' => 'Database seed script',
    '.htaccess' => 'URL rewriting rules',
    'INSTALL.md' => 'Installation guide',
    'README.md' => 'Project readme'
];

foreach ($files as $file => $desc) {
    $path = __DIR__ . '/' . $file;
    $checks["File: $desc"] = [
        'required' => "File exists ($file)",
        'actual' => file_exists($path) ? 'Found' : 'Missing',
        'pass' => file_exists($path)
    ];
}

// Check exports folder
$exports_path = __DIR__ . '/exports';
$exports_exists = is_dir($exports_path);
$exports_writable = $exports_exists ? is_writable($exports_path) : false;

$checks['Exports Folder'] = [
    'required' => 'Writable directory for exports',
    'actual' => $exports_writable ? 'Exists & writable' : ($exports_exists ? 'Exists but not writable' : 'Missing'),
    'pass' => $exports_writable
];

// Calculate overall status
$all_pass = true;
foreach ($checks as $check) {
    if (!$check['pass']) {
        $all_pass = false;
        break;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Setup Verification</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            padding: 40px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2em;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
        }
        
        .status-pass {
            background: #d4edda;
            color: #155724;
        }
        
        .status-fail {
            background: #f8d7da;
            color: #721c24;
        }
        
        .checks-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .check-item {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        .check-item.pass {
            border-color: #4caf50;
            background: #f1f8f4;
        }
        
        .check-item.fail {
            border-color: #f44336;
            background: #fef5f5;
        }
        
        .check-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .check-details {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
        }
        
        .check-status {
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 8px;
        }
        
        .check-item.pass .check-status {
            background: #4caf50;
            color: white;
        }
        
        .check-item.fail .check-status {
            background: #f44336;
            color: white;
        }
        
        .next-steps {
            background: #ecf0f1;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }
        
        .next-steps h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .next-steps ol {
            color: #555;
            margin-left: 20px;
            line-height: 1.8;
        }
        
        .next-steps li {
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .next-steps code {
            background: #fff;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        
        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin-top: 20px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        
        .action-button:hover {
            background: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔷 BMS Setup Verification</h1>
            <p style="color: #666; margin-top: 10px;">Business Management System</p>
            <span class="status-badge <?= $all_pass ? 'status-pass' : 'status-fail' ?>">
                <?= $all_pass ? '✅ All Checks Passed' : '❌ Some Checks Failed' ?>
            </span>
        </div>
        
        <div class="checks-grid">
            <?php foreach ($checks as $name => $check): ?>
                <div class="check-item <?= $check['pass'] ? 'pass' : 'fail' ?>">
                    <div class="check-name"><?= htmlspecialchars($name) ?></div>
                    <div class="check-details">
                        <strong>Required:</strong> <?= htmlspecialchars($check['required']) ?>
                    </div>
                    <div class="check-details">
                        <strong>Actual:</strong> <?= htmlspecialchars($check['actual']) ?>
                    </div>
                    <span class="check-status">
                        <?= $check['pass'] ? '✓ Pass' : '✗ Fail' ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="next-steps">
            <h3>📋 Next Steps</h3>
            <?php if ($all_pass): ?>
                <ol>
                    <li>Install PHP dependencies: <code>composer install</code></li>
                    <li>Import database schema: <code>php database/seed.php</code></li>
                    <li>Update config credentials if needed in <code>config/constants.php</code></li>
                    <li>Visit <a href="<?= BASE_URL ?>auth/login.php" style="color: #667eea;">login page</a></li>
                    <li>Login with: admin@bms.com / admin123 (change password!)</li>
                </ol>
                <button class="action-button" onclick="window.location.href='<?= BASE_URL ?>auth/login.php';">
                    Go to Login →
                </button>
            <?php else: ?>
                <ol>
                    <li>Review failed checks above</li>
                    <li>Refer to <a href="<?= BASE_URL ?>INSTALL.md" style="color: #667eea;">INSTALL.md</a> for solutions</li>
                    <li>Ensure MySQL server is running</li>
                    <li>Verify database credentials in <code>config/constants.php</code></li>
                    <li>Create <code>exports</code> folder with write permissions</li>
                    <li>Run this verification again after fixes</li>
                </ol>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
