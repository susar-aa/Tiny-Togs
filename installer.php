<?php
session_start();

$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = $_POST['host'] ?? 'localhost';
    $dbname = $_POST['dbname'] ?? 'tiny_togs';
    $username = $_POST['username'] ?? 'suzxlabs';
    $password = $_POST['password'] ?? 'Susara@200611003614';
    
    try {
        // Connect to MySQL
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        
        $files = [
            'Master Data' => __DIR__ . '/product_category_val.sql',
            'Staff Roster' => __DIR__ . '/tiny_togs_roster.sql'
        ];
        
        $successCount = 0;
        
        foreach ($files as $name => $path) {
            if (!file_exists($path)) {
                $message .= "<p>❌ <strong>$name:</strong> File not found ($path).</p>";
                continue;
            }
            
            // Read the entire file
            $sql = file_get_contents($path);
            
            // Execute the queries
            try {
                $pdo->exec($sql);
                $message .= "<p>✅ <strong>$name:</strong> Successfully imported.</p>";
                $successCount++;
            } catch (PDOException $e) {
                $message .= "<p>❌ <strong>$name Error:</strong> " . $e->getMessage() . "</p>";
            }
        }
        
        if ($successCount === 2) {
            $status = 'success';
            $message = "<h3>All systems imported successfully!</h3>" . $message;
            $message .= "<br><div class='alert warning'><strong>SECURITY WARNING:</strong> Please delete this <code>installer.php</code> file from your server immediately!</div>";
        } else {
            $status = 'error';
        }
        
    } catch (PDOException $e) {
        $status = 'error';
        $message = "<h3>Database Connection Failed</h3><p>" . $e->getMessage() . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Togs - Database Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --secondary: #ec4899;
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.7);
            --card-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        .background-blobs {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
            filter: blur(80px);
        }

        .blob {
            position: absolute;
            border-radius: 50%;
        }

        .blob-1 {
            width: 400px; height: 400px;
            background: var(--primary);
            top: -10%; left: 10%;
            opacity: 0.5;
        }

        .blob-2 {
            width: 350px; height: 350px;
            background: var(--secondary);
            bottom: -10%; right: 10%;
            opacity: 0.4;
        }

        .container {
            width: 100%;
            max-width: 500px;
            z-index: 10;
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .header i {
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        h2 { margin: 0 0 0.5rem 0; }
        p { color: var(--text-muted); margin: 0; }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--card-border);
            background: rgba(15, 23, 42, 0.6);
            color: var(--text-main);
            font-family: inherit;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus {
            border-color: var(--primary);
        }

        button {
            width: 100%;
            padding: 1rem;
            border-radius: 10px;
            border: none;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            font-family: inherit;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, opacity 0.2s;
            margin-top: 1rem;
        }

        button:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .message-box {
            margin-top: 2rem;
            padding: 1.5rem;
            border-radius: 10px;
            background: rgba(0,0,0,0.3);
            border: 1px solid var(--card-border);
        }

        .message-box.success { border-left: 4px solid #10b981; }
        .message-box.error { border-left: 4px solid #ef4444; }

        .alert {
            background: rgba(239, 68, 68, 0.2);
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #ef4444;
            color: #fca5a5;
        }
    </style>
</head>
<body>
    <div class="background-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
    </div>

    <div class="container">
        <div class="card">
            <div class="header">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <h2>Database Installer</h2>
                <p>Deploy Tiny Togs to your Server</p>
            </div>

            <?php if ($message): ?>
                <div class="message-box <?php echo $status; ?>">
                    <?php echo $message; ?>
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="host">Database Host</label>
                        <input type="text" id="host" name="host" value="localhost" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="dbname">Database Name</label>
                        <input type="text" id="dbname" name="dbname" value="tiny_togs" required>
                    </div>

                    <div class="form-group">
                        <label for="username">Database Username</label>
                        <input type="text" id="username" name="username" value="suzxlabs" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Database Password</label>
                        <input type="password" id="password" name="password" value="Susara@200611003614" required>
                    </div>

                    <button type="submit">
                        <i class="fa-solid fa-bolt"></i> Run Import
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
