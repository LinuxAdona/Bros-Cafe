-- Add reference_number column to orders table for GCash transactions
-- Run this migration to add the reference number field

ALTER TABLE `orders` 
ADD COLUMN `reference_number` VARCHAR(100) NULL DEFAULT NULL AFTER `payment_method`;
