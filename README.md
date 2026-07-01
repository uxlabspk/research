# Code Hunt's Research Site

Modern PHP blog type platform for publishing AI engineering content. The app ships with a public-facing guides index, a single-article reader, and a password-protected admin CMS for creating, editing, publishing, and deleting posts.

## What It Does

- Public guides homepage with featured cards
- Markdown-powered article pages with syntax highlighting
- Admin dashboard with post stats and CRUD controls
- Draft and published post states
- Featured image upload support
- CSRF protection for admin actions
- Auto-generated unique slugs and reading-time estimates

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- Tailwind CSS via CDN
- EasyMDE for Markdown editing
- Parsedown for Markdown rendering
- Lucide icons
- Highlight.js for code blocks

## Project Structure

```text
index.php            Public guides homepage
post.php             Single post view
setup.php            One-time database bootstrap
config.php           Environment and database configuration
functions.php        Shared helpers and CRUD logic
admin/               Admin dashboard, auth, and post management
public/logo.png      Site favicon / logo
```

## Features

### Public site

- Grid-based guide listing
- Category, author, and reading-time metadata
- Article navigation to previous / next posts
- Markdown content rendering with safe mode enabled
- Code block highlighting

### Admin CMS

- Secure login session handling
- Dashboard with total, published, and draft counts
- Create / edit / delete posts
- Publish or save as draft
- Featured image upload and replacement
- Inline Markdown editor with preview, autosave, and image upload

## Setup

### 1. Configure the database

Update `config.php` or create a `.env` file in the project root with your database settings:

```env
DB_HOST=localhost
DB_NAME=guide
DB_USER=root
DB_PASS=root
SITE_NAME=Your Site Name
SITE_URL=http://localhost:8000
SESSION_NAME=your_site_name_admin_session
```

`SITE_URL` should match the URL where the project is hosted.

### 2. Run the bootstrap script

Open `setup.php` in your browser once after configuring the database.

It will:

- create the `posts` table
- create the `admins` table
- insert a default admin account
- add sample guide posts

Default admin credentials created by setup:

- Username: `admin`
- Password: `admin123`

Change these immediately after logging in.

Important: delete `setup.php` from the server after setup is complete.

### 3. Use the app

- Public site: `index.php`
- Admin login: `admin/login.php`
- Dashboard: `admin/dashboard.php`

## Local Development

If you are running the project locally with PHP's built-in server:

```bash
php -S localhost:8000
```

Then open:

- `http://localhost:8000/` for the site
- `http://localhost:8000/setup.php` for first-time setup

## Uploads

Uploaded images are stored in `uploads/` and served from `/uploads/`.

Supported formats:

- JPG
- PNG
- GIF
- WebP

Max upload size: 5 MB

## Content Workflow

1. Log in to the admin panel
2. Create a new post
3. Write content in Markdown
4. Add metadata such as category, excerpt, author, and featured image
5. Save as draft or publish immediately

The app automatically generates:

- a unique slug from the title
- a reading-time estimate based on content length

## Notes

- Markdown is rendered through Parsedown with safe mode enabled.
- CSRF tokens are enforced for create, edit, and delete actions.
- Featured images are deleted from disk when posts are removed or replaced.
- Posts with `draft` status do not appear on the public site.