# UIU NewsHub - Dynamic News & Alert System

**UIU NewsHub** is a centralized, real-time web portal designed for United International University (UIU) students, faculty, and administration. It bridges the communication gap by aggregating academic notices, event updates, research grants, and emergency alerts into a single, modern interface.

## 🚀 Key Features

### 🎓 **Student Portal**
*   **Personalized Dashboard**: View academic stats (CGPA, Credits) and relevant notices.
*   **Smart Feed**: Automatically prioritizes Academic and Notice category news.
*   **Real-Time Alerts**: A live ticker (AJAX-powered) for urgent updates like traffic or weather warnings without page reload.
*   **Search & Filter**: Advanced search by keywords, category, or date range.
*   **Trending News**: See the most viewed articles at a glance.

### 🛡️ **Admin & Moderator Panel**
*   **Dashboard**: Overview of total views, active alerts, and recent articles.
*   **Content Management**: Create rich-text news articles with category tagging.
*   **Alert Management**: System to broadcast Red/Yellow alerts to all students instantly.

### 🎨 **Modern UI/UX**
*   **Glassmorphism Design**: Built with **Tailwind CSS** for a premium, clean aesthetic.
*   **Responsive**: Fully mobile-optimized layout.
*   **Dynamic Placeholders**: Automatic color-coded images for articles missing thumbnails.

---

## 🛠️ Technology Stack

*   **Frontend**: HTML5, Tailwind CSS (CDN), JavaScript (Fetch API/AJAX).
*   **Backend**: PHP (PDO for secure database interactions).
*   **Database**: MySQL / MariaDB.
*   **Tools**: CKEditor 5 (Rich Text Editor).

---

## ⚙️ Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone https://github.com/MH-Shomik/uiu_newsHub.git
    ```
2.  **Setup Database**
    *   Open XAMPP Control Panel and start **Apache** and **MySQL**.
    *   Go to `http://localhost/phpmyadmin`.
    *   Create a database named `uiu_news_system`.
    *   Import the `uiu_news_hub.sql` file located in the root directory.

3.  **Run the Project**
    *   Move the project folder to `htdocs` (e.g., `D:\xampp\htdocs\NewsHub`).
    *   Open your browser and navigate to:
        `http://localhost/NewsHub`

---

## 🔑 Login Credentials (for Testing)

| Role | User ID / Email | Password | Access |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@uiu.ac.bd` | `123456` | Full Control |
| **Student** | `0112230424` | `mehedisk321` | Student Portal |
| **Moderator** | `registrar@uiu.ac.bd` | `123456` | Dashboard & Posting |

---

## 📂 Project Structure

```
/NewsHub
├── /api               # JSON endpoints for AJAX (e.g., get_alerts.php)
├── /includes          # Database connection (db_connect.php)
├── index.php          # Public Landing Page
├── login.php          # Authentication Page
├── dashboard.php      # Admin Dashboard
├── student_dashboard.php # Student Portal
├── search.php         # Search Engine
├── news_create.php    # Article Creation Form
└── uiu_news_hub.sql   # Database Schema
```

---

*Developed for the Advanced Web Programming Project.*