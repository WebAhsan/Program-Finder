# 🎓 Program Finder – WordPress Plugin

A lightweight, user-friendly WordPress plugin that allows users to search academic programs based on filters like location, degree, GPA, experience, and start month. Results are shown both in a beautiful grid and interactive map view (Leaflet.js powered).

![Program Finder Demo](https://github.com/WebAhsan/Program-Finder/blob/main/Finder-Programer.png)

---

## 🚀 Features

- 🔍 Search programs by:
  - Location (State)
  - Degree Name
  - Healthcare Experience
  - GPA Requirement
  - Start Month
- 🗺️ Interactive Map View (Leaflet)
- 🧾 Grid View Toggle
- ⚡ AJAX-based Filtering
- 📱 Responsive Bootstrap Layout
- 🧩 Simple Shortcode Integration

---

## 📦 Installation

1. Clone or download the plugin:
   ```bash
   git clone https://github.com/WebAhsan/Program-Finder.git
Upload it to your WordPress site's wp-content/plugins/ directory.

Activate the plugin from your WordPress admin dashboard.

🧪 Usage
Add the following shortcode to any page/post:

shortcode
Copy
Edit
[program_finder]
That’s it! The search form and result sections will appear.

🧠 Dependencies
The plugin relies on the following:

Bootstrap 5 (via CDN)

Leaflet.js for map display

jQuery (already included in WordPress)

Make sure your theme doesn't block these.

📂 Custom Post Type
You should have a custom post type named programs with the following meta fields:

Meta Key	Description
location	Program's location/state
degree_name	Degree name
healthcare_experience	Required years of experience
gpa	Minimum GPA requirement
start_month	Program's start month
fee	Tuition or fee info
website	External URL
lat and lng	Latitude & Longitude for the map
image	Featured image or logo

📸 Screenshots
(You can add screenshots here — ex: search form, map popup, grid results, etc.)

🤝 Author
👨‍💻 Developed by: Ashikul Ahsan
🔗 GitHub: WebAhsan/Program-Finder

📄 License
This plugin is open-sourced under the MIT License.
