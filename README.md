# English Proficiency Management System

A PHP + MySQL web system for recording, reviewing and reporting English-language activities in a school.

> **Important:** GitHub Pages cannot run this system, because it needs PHP and MySQL. GitHub stores the code, and a GitHub Action copies it to a PHP web host every time you push a change. The web host makes it live on the internet.

## What you need
1. A **web host with PHP 8.0+, MySQL, FTP and free SSL (https)**. Any cPanel shared hosting works. Free hosts such as InfinityFree can be used to test, but for real student data a small paid plan is more dependable.
2. A **GitHub account** (free).

## Step 1: Prepare the web host
1. In the hosting panel, create a **MySQL database**, a **database user**, and give that user all privileges on the database. Write down: database host (often `localhost`), database name, user and password.
2. Find your **FTP details**: server (for example `ftp.yourschool.com`), username, password, and the web folder (usually `/public_html/` or `/htdocs/`).
3. Turn on the free **SSL certificate** (https) for your domain.

## Step 2: Put the code on GitHub
1. On github.com click **New repository**. Name it, choose **Private** (recommended), and create it.
2. Upload everything from this folder. The easiest way is **GitHub Desktop**: *File > Add local repository* (or *Clone*), copy this folder's contents into it, then *Commit* and *Push*.
   - The hidden `.github` folder must be included. If you use the website upload and `.github` is missing, click *Add file > Create new file*, type `.github/workflows/deploy.yml` as the name, and paste the contents of that file.
3. Make sure the branch is called **main**.

## Step 3: Tell GitHub how to reach your host (kept secret)
In your repository: **Settings > Secrets and variables > Actions > New repository secret**. Add:

| Name | Example |
|---|---|
| `FTP_SERVER` | `ftp.yourschool.com` |
| `FTP_USERNAME` | your FTP username |
| `FTP_PASSWORD` | your FTP password |
| `FTP_DIR` | `/public_html/` (must end with `/`; use `/public_html/english/` for a sub-folder) |

Then open the **Actions** tab, choose **Deploy to web host**, and press **Run workflow**. After a minute the files are on your host.

## Step 4: Connect the database (once)
Your database password must never go to GitHub. On the host, open **File Manager**, copy `config.local.sample.php` to `config.local.php`, and put your real database host, name, user and password in it.

## Step 5: Install and sign in
1. Open `https://yourschool.com/check.php` and confirm all checks pass.
2. Open `https://yourschool.com/install.php` and create the school and the first CPD Coordinator account.
3. **Delete `install.php` from the repository** on GitHub (and let the Action run again) so it is not uploaded again. Then sign in at `https://yourschool.com/`.
4. As CPD Coordinator: create your recovery code (My account), register classes, create teacher accounts, then give people the link from **Share link**.

## Moving your existing XAMPP data to the host (optional)
On XAMPP use **CPD > Backup** to download your data. In the host's phpMyAdmin, select your database and **Import** that file (after Step 4). Then skip `install.php` and sign in with your existing CPD Coordinator account. Never put this backup file in the repository.

## Updating the system later
Change files in GitHub (or push from GitHub Desktop). The Action uploads only what changed. Uploaded files, the logo and `config.local.php` live only on the host and are never touched by the deploy.

## Optional: force https
If your host does not already redirect to https, add this to a file named `.htaccess` on the host (not in the repository, so local XAMPP keeps working):

```
RewriteEngine On
RewriteCond %{HTTPS} !=on
RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

## Keep it safe
- Use **HTTPS only** and **strong passwords**. The system holds student and teacher data.
- Keep the repository **Private** and never commit `config.local.php`.
- Use **CPD > Backup** regularly and keep the file outside the host.
- Upgrades to the database happen automatically on first load; always take a backup first.
