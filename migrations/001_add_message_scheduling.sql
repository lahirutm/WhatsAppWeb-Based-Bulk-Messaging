-- Migration: Add Message Scheduling Support
-- Created: 2026-01-19
-- Description: Adds scheduling capabilities to the messages table

-- Add new columns to messages table
ALTER TABLE messages 
ADD COLUMN scheduled_at DATETIME NULL AFTER created_at,
ADD COLUMN is_scheduled BOOLEAN DEFAULT 0 AFTER status;

-- Update status enum to include scheduled and cancelled
ALTER TABLE messages 
MODIFY COLUMN status ENUM('pending', 'scheduled', 'sent', 'failed', 'cancelled') DEFAULT 'pending';

-- Add index for efficient querying of scheduled messages
CREATE INDEX idx_scheduled ON messages(is_scheduled, scheduled_at, status);

-- Add index for user's scheduled messages lookup
CREATE INDEX idx_user_scheduled ON messages(user_id, is_scheduled, scheduled_at);
