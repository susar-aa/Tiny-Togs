<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiny Togs - System Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --blue: #007aff;
            --blue-light: #e8f3ff;
            --green: #34c759;
            --green-light: #e6f9ed;
            --purple: #af52de;
            --purple-light: #f3eafb;
            --bg: #f2f2f7;
            --card: #ffffff;
            --label: #1c1c1e;
            --secondary: #6b6b70;
            --tertiary: #aeaeb2;
            --separator: #e5e5ea;
            --radius: 20px;
            --shadow: 0 2px 20px rgba(0,0,0,0.07);
            --shadow-hover: 0 8px 40px rgba(0,0,0,0.12);
            --font: -apple-system, BlinkMacSystemFont, "Inter", "SF Pro Display", "Segoe UI", sans-serif;
        }

        html, body {
            height: 100%;
            font-family: var(--font);
            background: var(--bg);
            color: var(--label);
            -webkit-font-smoothing: antialiased;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
        }

        .portal {
            width: 100%;
            max-width: 960px;
        }

        /* Header */
        .portal-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .portal-logo {
            width: 72px;
            height: 72px;
            background: linear-gradient(145deg, #007aff, #5ac8fa);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            margin-bottom: 1.25rem;
            box-shadow: 0 8px 24px rgba(0, 122, 255, 0.3);
        }

        .portal-header h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: var(--label);
            margin-bottom: 0.5rem;
        }

        .portal-header p {
            font-size: 1rem;
            color: var(--secondary);
            font-weight: 400;
        }

        /* Cards Grid */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
        }

        .sys-card {
            background: var(--card);
            border-radius: var(--radius);
            padding: 2rem 1.75rem;
            text-decoration: none;
            color: var(--label);
            box-shadow: var(--shadow);
            border: 1.5px solid transparent;
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .sys-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .sys-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        /* Card 1 - Blue */
        .sys-card.blue:hover { border-color: rgba(0,122,255,0.3); }
        .sys-card.blue .card-icon-wrap { background: var(--blue-light); color: var(--blue); }
        .sys-card.blue .card-arrow { color: var(--blue); }

        /* Card 2 - Green */
        .sys-card.green:hover { border-color: rgba(52,199,89,0.35); }
        .sys-card.green .card-icon-wrap { background: var(--green-light); color: var(--green); }
        .sys-card.green .card-arrow { color: var(--green); }

        /* Card 3 - Purple */
        .sys-card.purple:hover { border-color: rgba(175,82,222,0.3); }
        .sys-card.purple .card-icon-wrap { background: var(--purple-light); color: var(--purple); }
        .sys-card.purple .card-arrow { color: var(--purple); }

        .card-icon-wrap {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 1.25rem;
            transition: transform 0.25s ease;
        }

        .sys-card:hover .card-icon-wrap {
            transform: scale(1.08);
        }

        .sys-card h2 {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.01em;
            margin-bottom: 0.6rem;
        }

        .sys-card p {
            font-size: 0.88rem;
            color: var(--secondary);
            line-height: 1.55;
            flex-grow: 1;
            margin-bottom: 1.5rem;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1rem;
            border-top: 1px solid var(--separator);
        }

        .card-label {
            font-size: 0.85rem;
            font-weight: 600;
        }

        .card-arrow {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }

        .sys-card:hover .card-arrow {
            transform: translateX(3px);
        }

        /* Footer */
        .portal-footer {
            text-align: center;
            margin-top: 2.5rem;
            font-size: 0.8rem;
            color: var(--tertiary);
            font-weight: 400;
        }

        /* Fade in animations */
        .fade-up {
            opacity: 0;
            transform: translateY(16px);
            animation: fadeUp 0.5s ease forwards;
        }
        .d1 { animation-delay: 0.05s; }
        .d2 { animation-delay: 0.15s; }
        .d3 { animation-delay: 0.25s; }
        .d4 { animation-delay: 0.35s; }

        @keyframes fadeUp {
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 700px) {
            .cards-grid { grid-template-columns: 1fr; }
            .portal-header h1 { font-size: 1.6rem; }
        }
    </style>
</head>
<body>

<div class="portal">
    <header class="portal-header fade-up d1">
        <div class="portal-logo">
            <i class="fa-solid fa-shirt"></i>
        </div>
        <h1>Tiny Togs Portal</h1>
        <p>Choose a system to get started</p>
    </header>

    <div class="cards-grid">

        <!-- Master Data -->
        <a href="Tiny%20Togs%20Master%20Data/" target="_blank" class="sys-card blue fade-up d2">
            <div class="card-icon-wrap">
                <i class="fa-solid fa-database"></i>
            </div>
            <h2>Master Data</h2>
            <p>Manage products, categories, suppliers, and inventory classification.</p>
            <div class="card-footer">
                <span class="card-label" style="color: var(--blue);">Open System</span>
                <div class="card-arrow" style="background: var(--blue-light);">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>
        </a>

        <!-- Staff Roster -->
        <a href="Tiny%20Togs%20Roaster/" target="_blank" class="sys-card green fade-up d3">
            <div class="card-icon-wrap">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <h2>Staff Roster</h2>
            <p>Manage employee shifts, schedules, leaves, and generate monthly rosters.</p>
            <div class="card-footer">
                <span class="card-label" style="color: var(--green);">Open System</span>
                <div class="card-arrow" style="background: var(--green-light);">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>
        </a>

        <!-- Label Printing -->
        <a href="Tiny%20Togs%20Label%20Printing/" target="_blank" class="sys-card purple fade-up d4">
            <div class="card-icon-wrap">
                <i class="fa-solid fa-tags"></i>
            </div>
            <h2>Label Printing</h2>
            <p>Decode batch codes and print Aveeno stickers optimized for Zebra ZD230.</p>
            <div class="card-footer">
                <span class="card-label" style="color: var(--purple);">Open System</span>
                <div class="card-arrow" style="background: var(--purple-light);">
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </div>
        </a>

    </div>

    <div class="portal-footer fade-up d4">
        Tiny Togs &mdash; Internal Systems Portal &bull; v2.0
    </div>
</div>

</body>
</html>
