# 🚀 Deploy UCRD Management System on Vercel

## Prerequisites
- GitHub account with your repository
- Vercel account (free tier available)
- Git installed locally

## Step-by-Step Deployment Guide

### 1. **Prepare Your Repository**

Your repository is already on GitHub at: `https://github.com/848deepak/UCRD-Managment-System`

### 2. **Database Considerations**

**Important**: Vercel's serverless environment has limitations for SQLite:
- File system is read-only in production
- SQLite database files can't be written to
- Sessions may not persist

**Solutions**:

#### Option A: Use Vercel Postgres (Recommended)
```bash
# Add Vercel Postgres to your project
vercel storage add postgres
```

#### Option B: Use External Database
- **PlanetScale** (MySQL-compatible, free tier)
- **Supabase** (PostgreSQL, free tier)
- **Railway** (PostgreSQL, free tier)

### 3. **Deploy to Vercel**

#### Method 1: Via Vercel Dashboard
1. Go to [vercel.com](https://vercel.com)
2. Click "New Project"
3. Import your GitHub repository
4. Select the repository: `UCRD-Managment-System`
5. Configure settings:
   - **Framework Preset**: Other
   - **Root Directory**: `UCRD Management System`
   - **Build Command**: Leave empty
   - **Output Directory**: Leave empty
6. Click "Deploy"

#### Method 2: Via Vercel CLI
```bash
# Install Vercel CLI
npm i -g vercel

# Login to Vercel
vercel login

# Deploy
vercel

# Follow the prompts:
# - Link to existing project? No
# - Project name: ucrd-management-system
# - Directory: UCRD Management System
```

### 4. **Environment Variables**

Set these in Vercel Dashboard → Settings → Environment Variables:

```env
# Database (if using external database)
DB_HOST=your-db-host
DB_NAME=your-db-name
DB_USER=your-db-user
DB_PASS=your-db-password

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=notifications@yourdomain.com
```

### 5. **Update Database Configuration**

Replace `db.php` with `db_mysql.php` and update connection details:

```php
// Use environment variables
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'ucrd_management';
$username = $_ENV['DB_USER'] ?? 'root';
$password = $_ENV['DB_PASS'] ?? '';
```

### 6. **Handle File Uploads**

Since Vercel has read-only file system, you'll need to use external storage:

#### Option A: Vercel Blob Storage
```bash
vercel storage add blob
```

#### Option B: Cloud Storage
- **AWS S3**
- **Google Cloud Storage**
- **Cloudinary** (for images)

### 7. **Custom Domain (Optional)**

1. Go to Vercel Dashboard → Domains
2. Add your custom domain
3. Update DNS records as instructed

## 🎯 Vercel Advantages

✅ **Free Tier**: 100GB bandwidth, 100 serverless function executions/day
✅ **Automatic Deployments**: Deploy on every Git push
✅ **Global CDN**: Fast loading worldwide
✅ **SSL Certificate**: Automatic HTTPS
✅ **Preview Deployments**: Test changes before going live

## ⚠️ Limitations & Considerations

❌ **No Persistent File Storage**: Can't write to local files
❌ **Cold Starts**: First request may be slower
❌ **Function Timeout**: 10 seconds for hobby plan
❌ **No Background Jobs**: Email sending needs external service

## 🔧 Recommended Modifications

### 1. **Use External Email Service**
```php
// Use services like:
// - SendGrid
// - Mailgun
// - Resend
// - AWS SES
```

### 2. **Session Management**
```php
// Use Redis or database for sessions
session_save_handler(new DatabaseSessionHandler());
```

### 3. **File Uploads**
```php
// Use external storage
$uploader = new CloudStorageUploader();
$url = $uploader->upload($_FILES['file']);
```

## 🚀 Quick Start Commands

```bash
# Clone your repository
git clone https://github.com/848deepak/UCRD-Managment-System.git

# Install Vercel CLI
npm i -g vercel

# Deploy
cd "UCRD Management System"
vercel

# Your app will be live at: https://your-project.vercel.app
```

## 📞 Support

If you encounter issues:
1. Check Vercel logs in dashboard
2. Ensure all environment variables are set
3. Verify database connection
4. Check function timeout settings

Your UCRD Management System will be live and accessible worldwide! 🌍 