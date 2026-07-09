<?php
/**
 * One-time helper to set the demo account passwords after importing database.sql.
 * The seed hashes in the SQL file are placeholders and will NOT log in.
 *
 *   Admin:      admin@jobportal.test  / Admin@123
 *   Candidate:  john@example.com      / User@123
 *
 * !!! DELETE THIS FILE after running it once. !!!
 */

declare(strict_types=1);
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/plain; charset=utf-8');

if (is_file(__DIR__ . '/.reset_done')) {
    exit("Already run. Delete reset_passwords.php (and .reset_done) now.\n");
}

$adminHash = password_hash('Admin@123', PASSWORD_DEFAULT);
$userHash  = password_hash('User@123',  PASSWORD_DEFAULT);

$a = q("UPDATE admins SET password = ? WHERE email = ?", [$adminHash, 'admin@jobportal.test'])->rowCount();
$u = q("UPDATE users  SET password = ? WHERE email = ?", [$userHash,  'john@example.com'])->rowCount();

@file_put_contents(__DIR__ . '/.reset_done', date('c'));

echo "Done.\n";
echo "Admin rows updated:     $a  (admin@jobportal.test / Admin@123)\n";
echo "Candidate rows updated: $u  (john@example.com / User@123)\n\n";
echo "IMPORTANT: delete reset_passwords.php now for security.\n";
