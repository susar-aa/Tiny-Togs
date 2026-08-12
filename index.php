<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Togs - Unified Portal</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="background-blobs">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>

    <div class="container">
        <header class="hero">
            <h1 class="fade-in">Tiny Togs System Portal</h1>
            <p class="fade-in delay-1">Select the system you want to access below</p>
        </header>

        <div class="cards-wrapper">
            <!-- Master Data Card -->
            <a href="Tiny%20Togs%20Master%20Data/" class="card fade-in delay-2">
                <div class="card-icon">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h2>Master Data</h2>
                <p>Manage products, categories, suppliers, and overall inventory settings.</p>
                <div class="card-action">
                    <span>Enter System</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <!-- Roster Card -->
            <a href="Tiny%20Togs%20Roaster/" class="card fade-in delay-3">
                <div class="card-icon">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>
                <h2>Staff Roster</h2>
                <p>Manage employee shifts, schedules, leaves, and generate monthly rosters.</p>
                <div class="card-action">
                    <span>Enter System</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>
        </div>
    </div>
</body>
</html>
