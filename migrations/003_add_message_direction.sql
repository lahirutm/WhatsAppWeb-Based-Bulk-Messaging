-- Migration 003: Add direction to messages
ALTER TABLE messages ADD COLUMN direction ENUM('outbound', 'inbound') DEFAULT 'outbound' AFTER phone;
CREATE INDEX idx_phone_created ON messages (phone, created_at);
