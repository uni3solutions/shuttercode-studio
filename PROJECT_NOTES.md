# ShutterCode Studio — Project Documentation & Notes

## Project Overview
**ShutterCode Studio** is a modern photography shoot management system designed with a clean SaaS admin interface following Laravel MVC architecture, Tailwind CSS, Blade components, Vanilla JavaScript, and MySQL / SQLite.

The system supports 5 user roles:
1. **Super Admin** — Full system access, configuration, finance, analytics, and user management.
2. **Admin** — Shoot management, assignments, package creation, customer records, and operations.
3. **Telecaller** — Enquiries, lead tracking, follow-ups, and customer communications.
4. **Photographer** — Assigned shoots, availability, upload of gallery links, and payment status.
5. **Customer** — Booking viewing, invoice payment, and secure gallery photo access.

---

## Technology Stack & Environment
- **Backend**: Laravel (PHP 8.4+)
- **Architecture**: MVC (Model, View, Controller)
- **Database**: MySQL / SQLite for development
- **Templates**: Blade
- **Styling**: Tailwind CSS
- **Frontend Behavior**: Vanilla JavaScript
- **Icons**: Lucide Icons
- **Environment Details**:
  - PHP: PHP 8.4.21 (`C:\Users\prahu\.config\herd\bin\php84\php.exe`)
  - Composer: Composer 2.9.5 (`C:\Users\prahu\.config\herd\bin\composer.phar`)
  - Node.js: Node v26.2.0 (`C:\Users\prahu\.config\herd\bin\nvm\v26.2.0\node.exe`)
  - npm: npm 11.13.0 (`C:\Users\prahu\.config\herd\bin\nvm\v26.2.0\npm.cmd`)

---

## UI Design System & Tokens
- **Primary Color**: `#4F46E5` (Indigo-600 — buttons, active links, accents)
- **Primary Hover**: `#4338CA` (Indigo-700)
- **Primary Light**: `#EEF2FF` (Indigo-50 — active nav backgrounds, badges)
- **Workspace Background**: `#F8FAFC` (Slate-50 — page background)
- **Surface**: `#FFFFFF` (White — cards, tables, modals)
- **Borders**: `#E2E8F0` (Slate-200 — card borders, table separators)
- **Text Headings**: `#0F172A` (Slate-900)
- **Text Body**: `#334155` (Slate-700)
- **Text Muted**: `#64748B` (Slate-500)
- **Typography**: Inter / sans-serif with compact spacing and crisp hierarchy
- **Shadows**: Restrained, subtle elevation (`shadow-sm`, `shadow`)
- **Status Badges**: Small, rounded pills with light backgrounds (Success: Green, Pending: Amber, In Progress: Blue, Urgent: Red)

---

## Layout Rules
- **Sidebar**: Fixed-width desktop navigation with logo, grouped links, icons, active background, and user profile at the bottom.
- **Top Bar**: Page title, breadcrumbs, search/notifications, and primary action buttons.
- **Content Area**: Stat cards, responsive filters, searchable tables, pagination, and forms.
- **Components**: Reusable Blade components for buttons, input fields, badges, cards, tables, modal dialogs, and stats.

---

## Development Phases & Roadmap

### Phase 1: Project Setup & Design System Foundation
- [x] Initialize Laravel framework skeleton.
- [x] Configure environment and database connection (MySQL `shuttercode` + SQLite for testing).
- [x] Install and configure Tailwind CSS and Lucide icons.
- [x] Build reusable Blade components (`x-button`, `x-card`, `x-badge`, `x-input`, `x-table`, `x-modal`, `x-stat-card`, etc.).
- [x] Build base application layout (`app.blade.php`, sidebar, top bar, 1-click role switcher, responsive mobile menu).
- [x] Create base dashboard matching design reference.

### Phase 2: Authentication & Role-Based Dashboards
- [x] Laravel authentication setup (Login, Logout, 1-click role switcher for fast demoing).
- [x] Role policies and support for 5 personas: Super Admin, Admin, Telecaller, Photographer, Customer.
- [x] Role switcher in topbar to test application from all perspectives.

### Phase 3: Customer, Package & Lead Management
- [x] Customer CRUD (Profiles, contact info, booking history, direct WhatsApp CTA).
- [x] Photography Packages CRUD (Pricing, advance, duration, deliverables, crew count, status toggle).
- [x] Lead Management & Telecaller follow-up activities with Meta WhatsApp direct CTA link.
- [x] 1-Click "Convert Lead to Customer & Book Shoot" workflow.

### Phase 4: Shoot Booking & Photographer Workflow
- [x] Shoot Booking workflow with unique Shoot IDs (`SHT-YYYY-XXX`).
- [x] Automated package price calculation, advance deposit recording, and balance calculation.
- [x] Shoot Command Center (`shoots.show`) with visual pipeline stepper, logistics, crew assignments, and payment recording modal.
- [x] Photographer portal (`/my-shoots`) with assignment list, Google Drive RAW upload, and delivery submission.

### Phase 5: Payments, Invoices & Customer Portal
- [x] Payment transactions & automatic balance recalculation (advance, milestone, final settlement).
- [x] Photographer crew fee allocation and tracking.
- [x] Printable GST Tax Invoice generation (`invoices.show`) with CGST/SGST (18%) and bank details.
- [x] Private Client Gallery Portal (`/portal/gallery/{shootNumber}`) with PIN access, curated master highlights, and high-res RAW download links.

### Phase 6: WhatsApp, Notifications & Reports
- [x] WhatsApp direct click-to-chat links for leads and clients (`wa.me`).
- [x] Studio Activity Log audit stream on all major operations.
- [x] Studio Analytics & Reports (`/reports`) covering realized revenue, shoot distribution by event type, photographer workloads, and monthly cash flow.

### Phase 7: Testing & Verification
- [x] 21 automated feature tests covering all modules: CustomerTest, LeadTest, ShootTest, PaymentTest, PackageTest, InvoiceTest, ExampleTest (100% passing, 77 assertions).
- [x] Pint code style formatting verified.

---

## Vibe Coding Log & Daily Workflow
- **2026-10-18**:
  - Initialized Laravel project with MySQL `shuttercode` database via phpMyAdmin (`127.0.0.1:3306`, user `root`).
  - Configured 11 core tables and migrations: `users`, `customers`, `packages`, `leads`, `lead_activities`, `shoots`, `shoot_assignments`, `payments`, `photographer_payments`, `shoot_deliveries`, `invoices`, `activity_logs`.
  - Populated comprehensive mock dataset via `DatabaseSeeder.php`.
  - Built Tailwind CSS design system with custom tokens (`#4F46E5`, `#F8FAFC`, `#FFFFFF`, `#E2E8F0`).
  - Implemented 10 controllers: `AuthController`, `DashboardController`, `CustomerController`, `PackageController`, `LeadController`, `ShootController`, `PhotographerController`, `PaymentController`, `InvoiceController`, `ReportController`.
  - Created complete Blade views across all resources, including Shoot Command Center, Lead CRM, Tax Invoices, and Private Client Gallery.
  - Implemented comprehensive PHPUnit test suite: 21 tests, 77 assertions passing in ~2.1 seconds.
  - Formatted all PHP code with Laravel Pint to strict PSR-12 standard.

