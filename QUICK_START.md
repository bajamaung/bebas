# ⚡ Bangjo - Quick Start Guide

Panduan cepat untuk memulai Bangjo dalam 5 menit!

---

## 🚀 5-Minute Setup

### Step 1: Start XAMPP (30 seconds)
```
1. Buka XAMPP Control Panel
2. Klik START untuk Apache
3. Klik START untuk MySQL
```

### Step 2: Create Database (1 minute)
```
1. Buka http://localhost/phpmyadmin
2. Tab "Databases" → nama: bangjo_db → Create
3. Pilih bangjo_db
4. Tab "Import" → pilih config/database.sql → Go
```

### Step 3: Login (1 minute)
```
1. Buka http://localhost/bangjo
2. Redirect otomatis ke login page
3. Username: admin
4. Password: admin123
5. Klik LOGIN
```

### Step 4: Explore (2 minutes)
```
1. Lihat dashboard admin
2. Coba navigate sidebar
3. Explore fitur yang tersedia
```

---

## 🎯 Test All Roles

### Admin (Username: admin)
```
Password: admin123

Dashboard:
- View statistik sistem
- Lihat chart peminjaman
- Lihat timeline aktivitas

Management:
- Klik "User" untuk CRUD user
- Klik "Alat" untuk CRUD alat
- Klik "Kategori" untuk CRUD kategori

Monitoring:
- Klik "Peminjaman" untuk view semua loan
- Klik "Pengembalian" untuk return management
- Klik "Log" untuk view activity log
```

### Petugas (Username: petugas)
```
Password: admin123

Dashboard:
- View pending requests
- View chart peminjaman

Fitur:
- Klik "Approval" untuk approve/reject loan
- Klik "Monitoring" untuk track alat
- Klik "Laporan" untuk generate report
```

### Peminjam (Username: peminjam)
```
Password: admin123

Dashboard:
- View statistik personal
- View alat populer
- View recent loans

Katalog:
- Klik "Katalog" untuk browse alat
- Gunakan search/filter
- Klik "Pinjam" untuk ajukan peminjaman

Riwayat:
- Klik "Riwayat" untuk view loan history
- Lihat status peminjaman
```

---

## 📁 File Structure Quick Reference

```
bangjo/
├── login.php                          👈 Login page (starting point)
├── index.php                          👈 Home (redirects to dashboard)
├── config/
│   └── database.php                   👈 Database connection
├── includes/
│   ├── header.php                     👈 CSS & navbar
│   ├── sidebar.php                    👈 Left menu
│   └── navbar.php                     👈 Top bar
├── admin/
│   ├── index.php                      👈 Admin dashboard
│   ├── user.php, alat.php, etc.       👈 CRUD pages
├── petugas/
│   ├── index.php                      👈 Petugas dashboard
│   ├── approval.php                   👈 Approve loans
├── peminjam/
│   ├── index.php                      👈 User dashboard
│   ├── katalog.php                    👈 Browse equipment
│   └── riwayat.php                    👈 View history
└── assets/
    ├── css/style.css                  👈 Extra styles
    └── js/main.js                     👈 JavaScript functions
```

---

## ✨ Key Features Quick Test

### Test Login
```
1. Go to http://localhost/bangjo
2. Enter: admin / admin123
3. Should redirect to admin dashboard
```

### Test User Management
```
1. Login as admin
2. Click "User" in sidebar
3. Click "Tambah User"
4. Fill form (nama, username, password, role, status)
5. Click "Simpan"
6. Should appear in table
```

### Test Equipment Management
```
1. Login as admin
2. Click "Alat" in sidebar
3. Click "Tambah Alat"
4. Fill form (nama, kategori, stok, kondisi)
5. Note: Kode_alat auto-generated
6. Click "Simpan"
7. Should appear in table
```

### Test Approval
```
1. Login as peminjam
2. Click "Katalog"
3. Click "Pinjam" on any item
4. Fill dates & quantity
5. Click "Ajukan Peminjaman"
6. Logout & login as petugas
7. Click "Approval"
8. Should see pending request
9. Click "Approve" or "Reject"
```

---

## 🔐 Security Basics

### Passwords
- All demo accounts: `admin123`
- Passwords are bcrypt hashed
- Change passwords after first login!

### Sessions
- Auto-logout after 30 minutes idle
- Session destroyed on logout
- CSRF tokens on all forms

---

## ❓ Troubleshooting Quick Fixes

| Problem | Solution |
|---------|----------|
| Can't login | Check MySQL running, database imported |
| Page won't load | Check Apache running, verify file exists |
| CSS looks broken | Press Ctrl+F5 to clear cache |
| Database error | Verify database.sql imported correctly |
| Permission denied | Check file permissions, XAMPP settings |

---

## 📚 More Information

- **Full Setup**: See INSTALL.md
- **All Features**: See README.md
- **Project Details**: See PROJECT_SUMMARY.md
- **Version History**: See CHANGELOG.md

---

## 🎓 Learning Path

### Day 1: Explore
1. Login with all 3 roles
2. Explore each dashboard
3. Navigate all pages
4. Understand workflow

### Day 2: Test Features
1. Create new users
2. Add equipment & categories
3. Submit loan requests
4. Test approval system
5. View reports

### Day 3: Customize
1. Edit CSS colors/fonts
2. Change app name
3. Add company logo
4. Modify form fields
5. Add custom features

### Day 4: Deploy
1. Setup on production server
2. Change default passwords
3. Configure database backup
4. Setup monitoring
5. Test all features

---

## 🚀 Next Steps

1. **Explore the App**
   - Login with demo accounts
   - Test all features
   - Navigate all pages

2. **Understand the Code**
   - Read comments in PHP files
   - Check database schema
   - Review CSS structure

3. **Customize**
   - Change colors in CSS
   - Add company logo
   - Modify form fields
   - Add new features

4. **Deploy**
   - Setup on production
   - Configure backups
   - Setup monitoring

---

## 💡 Tips & Tricks

### Enable Logging
Check `admin/log.php` to see all system activities.

### Monitor System
Visit `admin/index.php` for system overview.

### Check Syntax
Use online PHP validators if getting errors.

### Test Features
Create test data in database before showing others.

### Backup Data
Export database regularly from PhpMyAdmin.

---

## 🎯 Success Metrics

You'll know it's working when:
- ✅ Can login with all 3 roles
- ✅ Dashboard shows correct data
- ✅ Can create/edit/delete items
- ✅ Approval workflow works
- ✅ Reports generate correctly
- ✅ Logs record activities

---

## 📞 Quick Help

**Still stuck?** Check these in order:
1. Browser console (F12)
2. XAMPP error log
3. PhpMyAdmin database
4. README.md
5. Code comments

---

## 🎉 You're Ready!

Everything is set up and ready to go.

**Enjoy Bangjo! 🚀**

---

**Pro Tips:**
- 💡 Bookmark `http://localhost/phpmyadmin` for database access
- 💡 Keep XAMPP running while using the app
- 💡 Check browser console (F12) if something breaks
- 💡 Read INSTALL.md for detailed setup
- 💡 Read README.md for all features

---

**Version**: 1.0.0
**Status**: ✅ Ready to Use
