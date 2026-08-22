# WP Courses Manager

> A WordPress plugin for managing courses with a custom post type (**Custom Post Type**), custom taxonomies, and a `[kursy]` shortcode featuring AJAX filtering, sorting, and search capabilities.

---

## Plugin Information

| Parameter | Value |
| :--- | :--- |
| **Contributors** | Asymphia |
| **Tags** | `courses`, `custom post type`, `events` |
| **Requires at least** | `6.0` |
| **Tested up to** | `6.7` |
| **Requires PHP** | `>= 7.4` |
| **Stable tag** | `1.0.0` |
| **License** | GPLv2 or later |

---

## Requirements

* **Advanced Custom Fields PRO** (ACF PRO)

---

## Features

Registers a custom post type (CPT) **"Kurs"** (*Course*) along with 4 custom taxonomies:
* **Kategoria** (*Category*)
* **Miejsce** (*Location / Venue*)
* **Organizator** (*Organizer*)
* **Wykładowca** (*Lecturer / Instructor*)

It also provides a versatile `[kursy]` shortcode featuring dynamic AJAX filtering without page reloads.

---

## Shortcode Usage

### Default call
```text
[kursy]
```

### Parameter Examples

* **Limit the number of courses and disable pagination:**
  ```text
  [kursy take="6" pagination="false"]
  ```

* **Filter by a specific category:**
  ```text
  [kursy category="silva-live-system"]
  ```

* **Display grid only (hide filters, sorting, and search bar):**
  ```text
  [kursy filters="false" sorting="false" search="false"]
  ```

> **Automatic Filtering:**  
> When placed on a course category archive page (e.g., `/course-category/name/`), the `[kursy]` shortcode automatically filters and displays courses from that category without requiring the `category` attribute.

---

## 📁 File Structure

```text
wp-courses-manager/
├── wp-courses-manager.php                  # Plugin startup file
├── assets/
│   ├── css/
│   │   └── course-styles.css               # Course grid and toolbar styles
│   └── js/
│       └── course-filters.js               # JS filters (AJAX, no reload)
└── includes/
    ├── class-admin-taxonomy-fields.php    # Select dropdowns in the course editor
    ├── class-ajax-handler.php             # AJAX filtering support
    ├── class-course-card.php              # Render single course card
    ├── class-course-query.php             # Building WP_Query + list rendering
    ├── class-helpers.php                  # Date formatting, lowest price, etc.
    ├── class-post-types.php               # Registering CPT "kurs"
    ├── class-shortcode.php                # Shortcode [kursy] and filter toolbar
    └── class-taxonomies.php               # Registering 4 taxonomies
```

---

## 🧩 Extension

Each element (a new taxonomy, new filter, or custom card layout) is a separate class in the `WPCourses` namespace.

### How to add a new feature:
1. Add a new class file in the `includes/` directory.
2. Require it in `wp-courses-manager.php`.
3. Attach it in the `init_modules()` method.