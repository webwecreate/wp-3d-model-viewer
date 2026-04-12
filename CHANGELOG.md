# CHANGELOG — WP 3D Model Viewer Plugin
> ห้ามเขียนทับ — เพิ่มรายการใหม่ด้านบนเสมอ (newest first)

---

## [1.1.2] — 2026-04-12 — Part 2: Admin + Settings

### Added
- `admin/class-wp3dmv-admin.php` v1.0.1
  - Register admin menu "3D Model Viewer" + submenu "Settings"
  - Enqueue admin CSS/JS scoped to plugin pages only
  - wp_localize_script: ajax_url, nonce, i18n
  - Plugin action link "Settings" on Plugins screen
  - Resolves Pending item from v1.1.1

- `admin/class-wp3dmv-settings.php` v1.0.1
  - WordPress Settings API, option key: wp3dmv_settings
  - Section General: default_bg_color, default_height, show_controls_hint, enable_fullscreen
  - Section 3D Viewer Defaults: auto_rotate, rotation_speed (slider 0.1–5), enable_zoom, camera_distance
  - Section Performance: lazy_load, max_texture_size (1024/2048/4096)
  - Full sanitisation per field type (hex color, float range, int range, whitelist)
  - get() / get_all() public API for other classes
  - Resolves Pending item from v1.1.1

---

## [1.1.1] — 2026-04-12 — Part 1 Hotfix (WC Removal)

### Changed
- `includes/class-wp3dmv-core.php` v1.0.0 → v1.0.1
  - ลบ `maybe_load` ของ `class-wp3dmv-product-meta.php` และ `class-wp3dmv-woocommerce.php`
  - ลบ `WP3DMV_Product_Meta` block ออกจาก `define_admin_hooks()`
  - ลบ method `define_woocommerce_hooks()` ออกทั้งหมด
  - ลบ `$this->define_woocommerce_hooks()` ออกจาก `__construct()`

- `includes/class-wp3dmv-activator.php` v1.0.0 → v1.0.1
  - ลบ `wc_default_position` และ `wc_tab_label` ออกจาก `$default_settings`

### Pending (จาก v1.1.0 — ยังค้างอยู่)
- `wp-3d-model-viewer.php` v1.0.0 → v1.0.1 (bump version)
- `class-wp3dmv-admin.php` v1.0.0 → v1.0.1 (ลบ WC references ถ้ามี)
- `class-wp3dmv-settings.php` v1.0.0 → v1.0.1 (ลบ WC section)

---

## [1.1.0] — 2026-04-12 — Architecture Revision (Remove WooCommerce)
### Changed
- Master Architecture อัปเดตเป็น v1.1.0
- Plugin เปลี่ยนเป็น standalone widget — ไม่ขึ้นกับ WooCommerce อีกต่อไป
- Compatible header: ลบ WooCommerce 8.x ออก เหลือแค่ WordPress 6.x | Elementor 3.x

### Removed
- `admin/class-wp3dmv-product-meta.php` — WC product metabox (ถอดออก)
- `woocommerce/class-wp3dmv-woocommerce.php` — WC integration class (ถอดออก)
- `woocommerce/templates/single-product-3d.php` — WC product template (ถอดออก)
- WooCommerce hooks ทั้งหมดออกจาก `class-wp3dmv-core.php`
- `define_woocommerce_hooks()` method ออกจาก `class-wp3dmv-core.php`
- WC product metabox block ออกจาก `define_admin_hooks()` ใน core
- `wc_default_position`, `wc_tab_label` ออกจาก default settings ใน activator
- Section [WooCommerce] ออกจาก settings page schema
- `product_id` control และ `source=product` option ออกจาก Elementor widget
- Shortcode `[wp3dmv_viewer id="POST_ID"]` (WC product ref) — เหลือแค่ `url=""` parameter
- Database schema: ลบ wp_postmeta keys ทั้งหมด (`_wp3dmv_*`)

### Pending (ต้องแก้ไฟล์ที่ทำไปแล้ว)
- `class-wp3dmv-core.php` v1.0.0 → v1.0.1 (ลบ WC references)
- `class-wp3dmv-activator.php` v1.0.0 → v1.0.1 (ลบ WC settings)
- `wp-3d-model-viewer.php` v1.0.0 → v1.0.1 (bump version)
- `class-wp3dmv-admin.php` v1.0.0 → v1.0.1 (ลบ WC references ถ้ามี)
- `class-wp3dmv-settings.php` v1.0.0 → v1.0.1 (ลบ WC section)

---

## [1.0.1] — 2026-04-06 — Part 1
### Added
- wp-3d-model-viewer.php — Main plugin file; constants, PHP/WP version checks, boot via wp3dmv()
- includes/class-wp3dmv-loader.php — Hook loader; collects actions/filters and registers them with WordPress
- includes/class-wp3dmv-activator.php — Activation handler; writes default options, validates environment
- includes/class-wp3dmv-deactivator.php — Deactivation handler; flushes rewrite rules (options kept)
- includes/class-wp3dmv-i18n.php — Text domain loader
- includes/class-wp3dmv-core.php — Singleton bootstrap; loads all modules, wires hooks via Loader
- uninstall.php — Removes all options and post meta on plugin deletion

---

## [1.0.0] — 2026-04-06
### Added
- สร้าง Master Architecture v1.0.0
- กำหนด file structure, class naming convention
- กำหนด database schema (wp_options + wp_postmeta)
- กำหนด hooks & filters map
- กำหนด JavaScript architecture (Three.js r158 + OrbitControls)
- กำหนด Elementor widget controls schema
- กำหนด Settings page schema
- กำหนด Chat Splitting Guide (10 chats)
- กำหนด Version Tracking Table
- กำหนด Security Checklist
- สร้าง CHANGELOG.md ไฟล์นี้

### Notes
- เริ่มต้นโปรเจกต์ — ยังไม่มีโค้ด PHP/JS จริง
- Phase 1 (Core) จะเริ่มใน Chat ถัดไป

---
*Format: [version] — date | Added / Changed / Fixed / Removed / Security*
