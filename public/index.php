<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Time Attendance</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="top">
    <h1>Time Attendance</h1>
    <div class="clock" aria-live="off"><span id="clockDate"></span> <strong id="clockTime"></strong></div>
</header>

<main>
    <section class="card">
        <h2>Record attendance</h2>
        <form id="form" novalidate>
            <div class="grid">
                <label>Employee ID
                    <input name="employee_id" required maxlength="32" autocomplete="off">
                    <small class="err" data-for="employee_id"></small>
                </label>
                <label>Full name
                    <input name="full_name" required maxlength="100" autocomplete="name">
                    <small class="err" data-for="full_name"></small>
                </label>
                <label>Department
                    <input name="department" required maxlength="80">
                    <small class="err" data-for="department"></small>
                </label>
                <label>Phone (optional)
                    <input name="phone" type="tel" maxlength="20" autocomplete="tel">
                    <small class="err" data-for="phone"></small>
                </label>
            </div>
            <div class="actions">
                <button type="submit" data-type="check_in" class="btn in">Check in</button>
                <button type="submit" data-type="check_out" class="btn out">Check out</button>
            </div>
            <p id="msg" role="status"></p>
        </form>
        <p class="note">The date and time are recorded by the server, not by your device.</p>
    </section>

    <section class="card">
        <h2>Attendance records</h2>
        <div class="filters">
            <input type="date" id="date" aria-label="Date">
            <input type="search" id="search" placeholder="Search ID, name or department" aria-label="Search">
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Time</th><th>Employee ID</th><th>Name</th><th>Department</th><th>Type</th></tr></thead>
                <tbody id="rows"></tbody>
            </table>
        </div>
    </section>
</main>
<script src="app.js"></script>
</body>
</html>
