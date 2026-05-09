# BMS Layout System Documentation

## Overview
All pages in the BMS application now use a **centralized, consistent layout system** that ensures:
- The sidebar always stays on the left side
- The top navigation bar remains at the top
- The footer is always at the bottom
- Only the main content area changes between pages

## How It Works

### Page Structure (All Pages)
Every module page now follows this standardized pattern:

```php
<?php
// Required includes for functionality
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../classes/SomeClass.php';
require_once '../../auth/session.php';

$page_title = 'Page Title';  // Sets the page title in browser and topbar

// Your page logic here
// Fetch data, process forms, etc.

// Open the standard layout
include_once '../../includes/header.php';
include_once '../../includes/layout-start.php';
?>
<div class="container">
    <!-- YOUR CONTENT HERE -->
    <!-- This is the ONLY part that changes between pages -->
</div>
<?php
// Close the standard layout
include_once '../../includes/footer.php';
?>
```

## Layout System Files

### `includes/header.php`
- Opens HTML document
- Loads CSS stylesheets
- Opens `<body>` tag
- Called once per page at the start

### `includes/layout-start.php` ⭐ NEW
- Creates the grid layout wrapper
- Includes the sidebar (fixed on left)
- Includes the topbar (fixed at top)
- Opens the main content area
- All pages use this to ensure consistent layout

### `includes/sidebar.php`
- Navigation menu
- Brand name
- User profile info
- Uses sticky positioning to stay on left

### `includes/topbar.php`
- Page title display
- Business selector dropdown
- Notification bell
- User profile menu
- Uses sticky positioning to stay at top

### `includes/layout-end.php` ⭐ NEW
- Closes the main content area
- Closes the grid layout wrapper
- Called by footer.php

### `includes/footer.php`
- Includes layout-end.php (closes layout)
- Loads JavaScript files
- Closes `<body>` and `</html>` tags
- Called once per page at the end

## CSS Layout Structure

The layout uses CSS Grid for a perfect 2-column layout:

```css
.layout-wrapper {
    display: grid;
    grid-template-columns: 250px minmax(0, 1fr);  /* 250px sidebar + flexible content */
    min-height: 100vh;
    background: var(--color-surface);
}

.sidebar {
    position: sticky;
    top: 0;
    height: 100vh;  /* Full viewport height */
}

.topbar {
    position: sticky;
    top: 0;
    z-index: 10;
}

.main-content {
    padding: 1.5rem;
    overflow: auto;
}
```

## Benefits

✅ **Consistency**: All pages have identical layout structure
✅ **Maintainability**: Change layout in one place, affects all pages
✅ **Responsive**: Mobile breakpoints handled centrally
✅ **Performance**: Shared CSS/JS loaded once
✅ **New Feature Development**: Just use `layout-start` and `layout-end`

## Adding a New Page

To create a new page:

1. Create your module file (e.g., `/modules/mymodule/index.php`)
2. Add required includes and logic
3. Call `include_once '../../includes/header.php';`
4. Call `include_once '../../includes/layout-start.php';`
5. Add your content in a `<div class="container">` element
6. End with `<?php include_once '../../includes/footer.php'; ?>`

Example:
```php
<?php
require_once '../../config/constants.php';
require_once '../../config/database.php';
require_once '../../auth/session.php';

$page_title = 'My New Module';

// Your logic here...

include_once '../../includes/header.php';
include_once '../../includes/layout-start.php';
?>

<div class="container">
    <h2><?= $page_title ?></h2>
    <!-- Your content -->
</div>

<?php include_once '../../includes/footer.php'; ?>
```

## Sidebar Always Stays Left ✓

The sidebar is fixed to the left via CSS:
- Position: sticky (desktop) / absolute (mobile)
- Grid column: 250px fixed width
- Stays visible while scrolling content
- Responsive: hides on mobile, toggle button appears

## Pages Updated ✓

All module pages now use the centralized layout system:
- ✓ Dashboard
- ✓ Inventory (index, create, edit, low_stock)
- ✓ Sales (index, create, view)
- ✓ Profits
- ✓ Businesses (index, create, edit, view)
- ✓ Investments (index, create, edit)
- ✓ Reports (index, sales_report, inventory_report, profit_report)

## Responsive Behavior

The layout automatically adjusts on smaller screens:
- **Desktop (1024px+)**: Sidebar visible, 2-column layout
- **Tablet (768px-1023px)**: Sidebar still visible but narrower
- **Mobile (<768px)**: Sidebar hidden, toggle button appears to show/hide sidebar

