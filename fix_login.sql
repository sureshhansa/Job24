-- =====================================================================
--  FIX LOGIN — run this ONCE in phpMyAdmin (SQL tab) on the job_portal DB
--  if you already imported the old database.sql with placeholder hashes.
--
--  Sets the real demo passwords:
--      admin@jobportal.test  ->  Admin@123
--      john@example.com      ->  User@123
--
--  Safe & non-destructive: it only updates the two demo accounts.
-- =====================================================================

UPDATE `admins`
SET `password` = '$2b$10$oa0t3OZgUizxcttk/kKrnuCO0I7ke6haRRmfuks/qJ9d0pdos3Hj6'
WHERE `email` = 'admin@jobportal.test';

UPDATE `users`
SET `password` = '$2b$10$knezfmQdrNBzDDxku8HwE.ebNpvrJUV.NS9UBBmB6AE1jVz5pTVlK'
WHERE `email` = 'john@example.com';
