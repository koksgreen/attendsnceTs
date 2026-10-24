<?php
// Edit these values (or set the environment variables) to match your MySQL server.
return [
    'db_host' => getenv('DB_HOST') ?: '127.0.0.1',
    'db_name' => getenv('DB_NAME') ?: 'attendance_db',
    'db_user' => getenv('DB_USER') ?: 'root',
    'db_pass' => getenv('DB_PASS') ?: '',
    'timezone' => 'Africa/Lagos',
];
