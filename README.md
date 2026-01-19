# WhatsApp Web-Based Bulk Messaging System

A comprehensive WhatsApp messaging platform built with PHP and Node.js, featuring role-based access control, message credit management, and RESTful API integration.

## 🚀 Features

### Core Messaging Features
- **Individual Messaging**: Send single messages to WhatsApp contacts with optional media attachments
- **Bulk Messaging**: Send messages to multiple recipients simultaneously via CSV upload or manual input
- **Media Support**: Attach images to both individual and bulk messages
- **Message Queue System**: Automatic rate-limited message processing (10-second delay between messages)
- **Message History**: Track individual and bulk message history with filtering options
- **Batch Tracking**: Monitor the status of bulk message campaigns in real-time

### WhatsApp Integration
- **Multi-Instance Support**: Link multiple WhatsApp accounts per user
- **QR Code Authentication**: Secure WhatsApp Web authentication via Socket.io
- **Session Persistence**: Automatic session restoration on server restart
- **Connection Management**: Real-time connection status monitoring
- **Duplicate Prevention**: Prevents linking the same phone number to multiple instances

### User Management & Access Control
- **Role-Based Access Control (RBAC)**: Four distinct user roles
  - **Client**: Basic messaging access
  - **Reseller**: Can create and manage client accounts
  - **Administrator**: Can create clients and resellers
  - **Super User**: Full system access
- **User Hierarchy**: Resellers and administrators can only view/manage users they created
- **Account Management**: Enable/disable user accounts, password management
- **User Creation**: Role-based user creation with appropriate permissions

### Credit System
- **Message Credits**: Pre-paid credit system for message sending
- **Credit Management**: Administrators and resellers can add/set credits for their users
- **Automatic Deduction**: Credits automatically deducted when messages are queued
- **Insufficient Credit Protection**: Prevents message sending when credits are insufficient
- **Credit Tracking**: Real-time credit balance display and transaction tracking

### API Integration
- **RESTful API**: Send messages programmatically via HTTP POST
- **API Key Authentication**: Secure 64-character API keys per instance
- **API Key Management**: Generate and regenerate API keys through the web interface
- **Credit-Aware API**: API requests also consume message credits
- **JSON Response Format**: Structured responses with status and credit information

### Security Features
- **Secure Authentication**: Password hashing with PHP's password_hash()
- **Session Management**: Secure session handling with custom session paths
- **Database Transactions**: ACID-compliant credit deduction and message queuing
- **Row-Level Locking**: Prevents race conditions in credit management
- **Input Validation**: Comprehensive validation on all user inputs
- **CSRF Protection**: Form-based security measures

### Monitoring & Reporting
- **Dashboard**: Overview of all linked instances and their connection status
- **Message Status Tracking**: Track messages as pending, sent, or failed
- **Batch Statistics**: Real-time statistics for bulk message campaigns
- **History Filtering**: Filter message history by phone number and date range
- **API Tracking**: Separate tracking for API-sent vs. web-sent messages

## 📋 Requirements

### Server Requirements
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher
- **Node.js**: 14.x or higher
- **Web Server**: Apache/Nginx
- **PHP Extensions**:
  - PDO
  - pdo_mysql
  - curl
  - json
  - session

### Node.js Dependencies
- `whatsapp-web.js`: WhatsApp Web API integration
- `express`: Web server framework
- `socket.io`: Real-time bidirectional communication
- `mysql2`: MySQL database driver
- `qrcode-terminal`: QR code generation for terminal
- `dotenv`: Environment variable management

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd wwebjs
```

### 2. Database Setup
```bash
mysql -u root -p
```

```sql
CREATE DATABASE whatsapp_app;
USE whatsapp_app;
SOURCE schema.sql;
```

### 3. Configure Database Connection

**For PHP** - Create/edit `src/Database.php`:
```php
private $host = "localhost";
private $db_name = "whatsapp_app";
private $username = "root";
private $password = "your_password";
```

**For Node.js** - Create `bot/.env`:
```env
DB_HOST=localhost
DB_USER=root
DB_PASSWORD=your_password
DB_NAME=whatsapp_app
PORT=3000
```

### 4. Install Node.js Dependencies
```bash
cd bot
npm install
```

### 5. Create Required Directories
```bash
mkdir -p public/uploads
mkdir -p sessions
chmod 755 public/uploads
chmod 755 sessions
```

### 6. Configure Web Server

**Apache** - Add to your VirtualHost or `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /public/index.php [QSA,L]
</IfModule>
```

**Nginx**:
```nginx
location / {
    try_files $uri $uri/ /public/index.php?$query_string;
}
```

### 7. Start the Node.js Service
```bash
cd bot
node server.js
```

For production, use PM2:
```bash
npm install -g pm2
pm2 start server.js --name whatsapp-bot
pm2 save
pm2 startup
```

## 🎯 Usage

### Initial Setup

1. **Register an Account**: Navigate to `/register` and create the first user (will be a super_user)
2. **Login**: Access the dashboard at `/login`
3. **Link WhatsApp Account**: 
   - Click "Link New Account" on the dashboard
   - Scan the QR code with WhatsApp mobile app
   - Wait for connection confirmation

### Sending Individual Messages

1. Navigate to **Dashboard**
2. Click **Send Message** on a connected instance
3. Enter:
   - Phone number (with country code, e.g., +1234567890)
   - Message body
   - Optional: Upload an image
4. Click **Send Message**

### Sending Bulk Messages

1. Navigate to **Dashboard**
2. Click **Bulk Send** on a connected instance
3. Provide recipients via:
   - **CSV Upload**: Upload a CSV file with phone numbers in the first column
   - **Text Input**: Enter phone numbers (one per line)
4. Enter message body
5. Optional: Upload an image
6. Click **Send Bulk Messages**
7. Monitor progress on the batch status page

### Managing Users (Admin/Reseller)

1. Navigate to **Users** in the navigation menu
2. **Create New User**:
   - Click "Create New User"
   - Enter username and password
   - Select appropriate role
   - Set initial message credits
3. **Manage Existing Users**:
   - **Enable/Disable**: Toggle user account status
   - **Change Password**: Update user passwords
   - **Manage Credits**: Add or set message credits

### Using the API

#### Generate API Key
1. Navigate to **API Settings**
2. Select an instance
3. Click **Generate API Key**
4. Copy the generated key

#### Send Message via API
```bash
curl -X POST http://your-domain.com/api/send \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_64_character_api_key",
    "sender": "1234567890",
    "number": "0987654321",
    "message": "Hello from API!"
  }'
```

**Response (Success)**:
```json
{
  "status": "success",
  "message": "Message queued successfully",
  "message_id": 123,
  "credits_remaining": 95
}
```

**Response (Insufficient Credits)**:
```json
{
  "status": "error",
  "message": "Insufficient credits",
  "credits_available": 0,
  "credits_required": 1
}
```

## 📊 Database Schema

### Tables

#### `users`
- User accounts with role-based permissions
- Password hashing and authentication
- Message credit balance
- Hierarchical user relationships (created_by)

#### `instances`
- WhatsApp account instances
- Session management
- API key storage
- Connection status tracking

#### `batches`
- Bulk message campaign tracking
- Total message count per batch
- User and instance associations

#### `messages`
- Individual message records
- Status tracking (pending/sent/failed)
- Media path storage
- API vs. web interface tracking

## 🔐 Security Best Practices

1. **Change Default Credentials**: Update database passwords immediately
2. **Use HTTPS**: Always use SSL/TLS in production
3. **Secure API Keys**: Store API keys securely and rotate regularly
4. **File Upload Validation**: Validate file types and sizes
5. **Rate Limiting**: Implement additional rate limiting for API endpoints
6. **Regular Backups**: Backup database and session files regularly
7. **Update Dependencies**: Keep all dependencies up to date

## 🐛 Troubleshooting

### WhatsApp Won't Connect
- Ensure Node.js service is running (`pm2 status`)
- Check browser console for Socket.io errors
- Verify port 3000 is accessible
- Clear `.wwebjs_auth` and `.wwebjs_cache` directories

### Messages Not Sending
- Verify instance status is "connected"
- Check message queue: `SELECT * FROM messages WHERE status = 'pending'`
- Review Node.js logs: `pm2 logs whatsapp-bot`
- Ensure sufficient message credits

### Database Connection Errors
- Verify MySQL service is running
- Check database credentials in both PHP and Node.js configs
- Ensure database user has proper permissions

### API Returns 401 Unauthorized
- Verify API key is correct
- Ensure instance is connected
- Check that sender number matches the linked WhatsApp account

## 📁 Project Structure

```
wwebjs/
├── bot/                          # Node.js WhatsApp service
│   ├── server.js                 # Main server with Socket.io & queue worker
│   ├── db.js                     # Database connection pool
│   ├── package.json              # Node.js dependencies
│   └── .env                      # Environment variables
├── public/                       # Public web directory
│   ├── index.php                 # Main router
│   └── uploads/                  # Media file uploads
├── src/                          # PHP application source
│   ├── Database.php              # Database connection class
│   └── Controllers/              # MVC Controllers
│       ├── ApiController.php     # API message sending
│       ├── ApiSettingsController.php  # API key management
│       ├── AuthController.php    # Login/registration
│       ├── BulkController.php    # Bulk messaging
│       ├── DashboardController.php    # Main dashboard
│       ├── HistoryController.php      # Message history
│       ├── InstanceController.php     # WhatsApp instance management
│       ├── MessageController.php      # Individual messaging
│       └── UserController.php         # User management
├── views/                        # PHP view templates
│   ├── dashboard.php             # Main dashboard
│   ├── send_message.php          # Individual message form
│   ├── bulk_send.php             # Bulk message form
│   ├── batch_status.php          # Batch tracking
│   ├── history_individual.php    # Individual message history
│   ├── history_bulk.php          # Bulk message history
│   ├── api_settings.php          # API key management
│   ├── link_account.php          # WhatsApp linking
│   ├── login.php                 # Login page
│   ├── register.php              # Registration page
│   └── users/                    # User management views
│       ├── index.php             # User list
│       ├── create.php            # Create user
│       ├── password.php          # Change password
│       └── credits.php           # Manage credits
├── sessions/                     # PHP session storage
├── schema.sql                    # Database schema
└── README.md                     # This file
```

## 🔄 Message Flow

1. **User/API submits message** → Message inserted into database with status "pending"
2. **Credits deducted** → Transaction ensures atomic credit deduction
3. **Queue worker processes** → Node.js service checks queue every 5 seconds
4. **Rate limiting applied** → 10-second delay between messages per instance
5. **WhatsApp sends message** → Message sent via whatsapp-web.js
6. **Status updated** → Database updated to "sent" or "failed"

## 🎨 User Roles & Permissions

| Feature | Client | Reseller | Administrator | Super User |
|---------|--------|----------|---------------|------------|
| Send Messages | ✅ | ✅ | ✅ | ✅ |
| View Own History | ✅ | ✅ | ✅ | ✅ |
| Link WhatsApp | ✅ | ✅ | ✅ | ✅ |
| Use API | ✅ | ✅ | ✅ | ✅ |
| Create Clients | ❌ | ✅ | ✅ | ✅ |
| Create Resellers | ❌ | ❌ | ✅ | ✅ |
| Create Admins | ❌ | ❌ | ❌ | ✅ |
| Manage Own Users | ❌ | ✅ | ✅ | ✅ |
| View All Users | ❌ | ❌ | ❌ | ✅ |

## 📝 License

This project is proprietary software. All rights reserved.

## 🤝 Support

For support, please contact your system administrator or refer to the project documentation.

## 🔮 Future Enhancements

- [x] **Message scheduling** ✅ IMPLEMENTED
- [x] **Template management** ✅ IMPLEMENTED
- [ ] Contact list management
- [ ] Advanced analytics and reporting
- [ ] Webhook support for message status updates
- [ ] Multi-language support
- [ ] Two-factor authentication
- [ ] Message delivery reports
- [ ] Group messaging support
- [ ] Auto-reply functionality

## 📝 Template Management (NEW)

Create and reuse message templates for faster messaging.

### Features
- **CRUD Operations**: Create, view, edit, and delete templates.
- **One-Click Selection**: Select templates from a dropdown in Send Message and Bulk Send pages.
- **AJAX Loading**: Template content is loaded instantly without refreshing the page.
- **Live Preview Integration**: Works with the real-time message formatter.

### Usage
1. Go to **Manage Templates** in the navigation menu.
2. Create your reusable templates.
3. When sending a message, use the **Message Template** dropdown to populate the body.


## 📅 Message Scheduling (NEW)

Schedule messages to be sent at a future date and time.

### Features
- **Individual Message Scheduling**: Schedule single messages via web interface
- **Bulk Message Scheduling**: Schedule entire batches to be sent at once
- **API Scheduling**: Schedule messages programmatically via API
- **Credit Management**: Credits deducted when scheduled (refunded if cancelled)
- **Manage Scheduled Messages**: View, cancel, or reschedule pending messages

### Usage

#### Web Interface
1. Navigate to Send Message or Bulk Send
2. Check "Schedule this message"
3. Select date and time using the datetime picker
4. Submit the form
5. View scheduled messages at `/scheduled-messages`

#### API
```bash
curl -X POST http://your-domain.com/api/send \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your_api_key",
    "sender": "1234567890",
    "number": "0987654321",
    "message": "This will be sent later",
    "scheduled_at": "2026-01-20T14:30:00+05:30"
  }'
```

**Response**:
```json
{
  "status": "success",
  "message": "Message scheduled successfully",
  "message_id": 123,
  "credits_remaining": 95,
  "scheduled_at": "2026-01-20 09:00:00"
}
```

### Managing Scheduled Messages
- **View All**: Navigate to "Scheduled Messages" in the menu
- **Cancel**: Click "Cancel" button (credit will be refunded)
- **Reschedule**: Click "Reschedule" to change the send time

### Database Migration
For existing installations, run the migration:
```bash
mysql -u root -p whatsapp_app < migrations/001_add_message_scheduling.sql
```

Then restart the Node.js service:
```bash
pm2 restart whatsapp-bot
```
