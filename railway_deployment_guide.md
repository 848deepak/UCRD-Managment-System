# 🚀 Deploy UCRD Management System on Railway

Railway is a modern hosting platform that's perfect for PHP applications. It's much more reliable than Vercel for PHP projects.

## 🎯 Why Railway is Better for PHP:

✅ **Native PHP Support** - No complex configuration needed  
✅ **Automatic Database Provisioning** - Built-in PostgreSQL  
✅ **Simple Deployment** - Just connect your GitHub repo  
✅ **Free Tier Available** - $5 credit monthly  
✅ **Custom Domains** - Easy domain setup  
✅ **Environment Variables** - Simple configuration  

## 📋 Prerequisites:

- GitHub account with your repository
- Railway account (free signup at [railway.app](https://railway.app))

## 🚀 Step-by-Step Deployment:

### 1. **Sign Up for Railway**
- Go to [railway.app](https://railway.app)
- Sign up with your GitHub account
- Get $5 free credit monthly

### 2. **Create New Project**
1. Click "New Project"
2. Select "Deploy from GitHub repo"
3. Choose your repository: `UCRD-Managment-System`
4. Railway will automatically detect it's a PHP project

### 3. **Add Database (Optional but Recommended)**
1. In your project, click "New"
2. Select "Database" → "PostgreSQL"
3. Railway will automatically provision a PostgreSQL database
4. Copy the database connection details

### 4. **Configure Environment Variables**
In your Railway project settings, add these environment variables:

```env
# Database (if using Railway PostgreSQL)
DB_HOST=your-railway-db-host
DB_NAME=your-railway-db-name
DB_USER=your-railway-db-user
DB_PASS=your-railway-db-password

# Or use the Railway PostgreSQL URL
DATABASE_URL=postgresql://user:pass@host:port/database

# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=notifications@yourdomain.com
```

### 5. **Deploy**
- Railway will automatically deploy your application
- Your app will be live at: `https://your-project-name.railway.app`

## 🔧 Database Configuration for Railway:

If you're using Railway's PostgreSQL, update your `db.php`:

```php
<?php
// Railway PostgreSQL configuration
$host = $_ENV['DB_HOST'] ?? 'localhost';
$dbname = $_ENV['DB_NAME'] ?? 'railway';
$username = $_ENV['DB_USER'] ?? 'postgres';
$password = $_ENV['DB_PASS'] ?? '';

try {
    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables...
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
```

## 🎯 Railway Advantages:

✅ **No Complex Configuration** - Works out of the box  
✅ **Automatic HTTPS** - SSL certificates included  
✅ **Global CDN** - Fast loading worldwide  
✅ **Auto-scaling** - Handles traffic spikes  
✅ **Built-in Monitoring** - Track performance  
✅ **Easy Rollbacks** - Deploy previous versions  

## 💰 Pricing:

- **Free Tier**: $5 credit monthly
- **Hobby Plan**: $5/month for 500 hours
- **Pro Plan**: $20/month for unlimited usage

## 🔄 Migration from Vercel:

1. **Keep your Vercel project** (as backup)
2. **Deploy to Railway** using this guide
3. **Test thoroughly** on Railway
4. **Update your domain** to point to Railway
5. **Remove Vercel deployment** if desired

## 📞 Support:

Railway has excellent documentation and support:
- [Railway Docs](https://docs.railway.app)
- [Community Discord](https://discord.gg/railway)
- [GitHub Issues](https://github.com/railwayapp/railway)

Your UCRD Management System will be live and working perfectly on Railway! 🎉 