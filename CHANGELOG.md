# CHANGELOG — WP 3D Model Viewer Plugin
> ห้ามเขียนทับ — เพิ่มรายการใหม่ด้านบนเสมอ (newest first)



---

## [1.8.0] — 2026-04-17 — Part 9: Bugfix #14 — GLB/GLTF upload blocked by WordPress

### Fixed
- `public/class-wp3dmv-public.php` v1.0.2 → v1.0.3
  - เพิ่ม `allow_3d_upload_mimes()` — hook: `upload_mimes`
    เพิ่ม `.glb` (model/gltf-binary) และ `.gltf` (model/gltf+json)
    เข้า allowed MIME types ของ WordPress
  - เพิ่ม `fix_3d_filetype_check()` — hook: `wp_check_filetype_and_ext`
    แก้กรณี finfo/mime_content_type คืน `application/octet-stream` สำหรับ .glb
    → WordPress reject upload ด้วย "This file cannot be processed"

- `includes/class-wp3dmv-core.php` v1.0.2 → v1.0.3
  - define_public_hooks(): register filter `upload_mimes` และ
    `wp_check_filetype_and_ext` (priority 10, 4 args)

---

## [1.7.9] — 2026-04-16 — Part 9: Bugfix #13 — Model squished after exit fullscreen

### Fixed
- `public/js/wp3dmv-viewer.js` v1.0.1 → v1.0.2
  - fullscreenchange handler: เพิ่ม setTimeout(onWindowResize, 50)
    หลัง exit fullscreen ทั้ง enter และ exit
    เดิมไม่ resize renderer → canvas ยังเป็น fullscreen size
    → model ดูเตี้ยผิดสัดส่วนหลังกลับมา

---

## [1.7.8] — 2026-04-16 — Part 9: Bugfix #10–#12

### Fixed
- `public/js/wp3dmv-viewer.js` v1.0.0 → v1.0.1
  - createRenderer(): เปลี่ยน outputColorSpace (r152+) → outputEncoding = THREE.sRGBEncoding (r147)
    model ดูสว่างและสีถูกต้อง
  - init(): เพิ่ม fullscreen button handler (requestFullscreen / exitFullscreen)
    พร้อม is-fullscreen class toggle

- `includes/class-wp3dmv-viewer.php` v1.0.1 → v1.0.2
  - merge_args(): เพิ่ม per-instance override สำหรับ rotation_speed, enable_zoom,
    show_controls_hint — เดิมดึงจาก global เสมอ widget/shortcode override ไม่ได้

- `elementor/widgets/class-widget-3d-viewer.php` v1.0.2 → v1.0.3
  - render(): แก้ key 'autorotate' → 'auto_rotate'
  - render(): แก้ key 'show_hint' → 'show_controls_hint'
    ให้ตรงกับที่ merge_args() รับ

---

## [1.7.7] — 2026-04-16 — Part 9: Bugfix #9 — Elementor widget panel ไม่ขึ้น

### Fixed
- `elementor/widgets/class-widget-3d-viewer.php` v1.0.1 → v1.0.2
  - content_template(): ลบ {{! }} comment syntax ออก 2 บรรทัด
    ไม่ใช่ syntax ของ Underscore.js/Elementor → JS error
    → editor render ไม่ได้ → widget panel ไม่แสดง → structure เห็น Empty

---

## [1.7.6] — 2026-04-16 — Part 9: Bugfix #7+#8 — Elementor widget renders empty

### Fixed
- `elementor/widgets/class-widget-3d-viewer.php` v1.0.0 → v1.0.1
  - render(): เพิ่ม echo หน้า WP3DMV_Viewer::render($args)
    เดิมไม่ echo → Elementor ไม่แสดงผล → เห็นแค่ empty container
  - render(): แก้ key 'bg' → 'bg_color' ให้ตรงกับ
    WP3DMV_Viewer::merge_args() ที่อ่าน $args['bg_color']

---

## [1.7.5] — 2026-04-16 — Part 9: Bugfix #4 + #6 — Settings not saving / not applying

### Fixed
- `admin/class-wp3dmv-settings.php` v1.0.1 → v1.0.2
  - render_page(): settings_errors(self::OPTION_KEY) → settings_errors()
    เดิม filter ผิด key → success notice ไม่แสดงหลัง save

- `includes/class-wp3dmv-viewer.php` v1.0.0 → v1.0.1
  - get_global_settings(): แก้ key 'default_auto_rotate' → 'auto_rotate'
    และ 'default_rotation_speed' → 'rotation_speed'
  - merge_args(): แก้การอ่าน $global_settings ให้ตรง key เดียวกัน
    เดิม key ไม่ตรงกับที่ Settings class save → ค่าที่ user ตั้งไม่มีผลเลย

---

## [1.7.4] — 2026-04-16 — Part 9: Bugfix #3 — Fatal error on plugin activation

### Fixed
- `includes/class-wp3dmv-core.php` v1.0.1 → v1.0.2
  - define_admin_hooks(): สร้าง WP3DMV_Settings ก่อน แล้วส่งเป็น
    argument ที่ 2 เข้า WP3DMV_Admin() constructor
    (เดิมส่งแค่ $this->version → ArgumentCountError fatal error)
  - แก้ชื่อ method hook จาก 'add_plugin_admin_menu' → 'register_admin_menu'
    ให้ตรงกับชื่อจริงใน class-wp3dmv-admin.php

---

## [1.7.3] — 2026-04-16 — Part 9: Bugfix #2 — data-settings JSON key mismatch

### Fixed
- `public/partials/viewer-template.php` v1.0.0 → v1.0.1
  - แก้ JSON keys ใน $data_settings จาก camelCase → snake_case
    ให้ตรงกับที่ wp3dmv-viewer.js / wp3dmv-controls.js / wp3dmv-loader.js อ่าน
  - autoRotate      → auto_rotate
  - rotationSpeed   → rotation_speed
  - enableZoom      → enable_zoom
  - cameraDistance  → initial_camera_distance  (ชื่อต่างกันด้วย)
  - เพิ่ม bg_color (string) — ก่อนหน้านี้ขาด → scene.background ใช้ default เสมอ

---

## [1.7.2] — 2026-04-15 — Part 9: Bugfix #1b — Downgrade Three.js vendor to r147

### Changed
- `public/class-wp3dmv-public.php` v1.0.1 → v1.0.2
  - เปลี่ยน version string ของ vendor scripts จาก '158' → '147'
    ครอบคลุม: wp3dmv-three, wp3dmv-orbit, wp3dmv-gltf ทั้ง 3 handles

### Added (asset files — replaced)
- `assets/vendor/three/three.min.js` — เปลี่ยนเป็น r147
- `assets/vendor/three/OrbitControls.js` — เปลี่ยนเป็น r147
- `assets/vendor/three/GLTFLoader.js` — เพิ่มใหม่ r147
  - Source: https://unpkg.com/three@0.147.0/examples/js/loaders/GLTFLoader.js

### Reason
- Three.js ลบ examples/js (legacy builds) ออกตั้งแต่ r148
- r158 ไม่มี GLTFLoader.js แบบ script tag อีกต่อไป
- r147 เป็น version สุดท้ายที่รองรับ THREE.GLTFLoader global ผ่าน script tag

---

## [1.7.1] — 2026-04-15 — Part 9: Bugfix #1 — Missing Script Enqueues

### Fixed
- `public/class-wp3dmv-public.php` v1.0.0 → v1.0.1
  - เพิ่ม `GLTFLoader.js` (handle: `wp3dmv-gltf`, dep: wp3dmv-three, ver: 158)
    ก่อนหน้านี้ขาด → `THREE.GLTFLoader` undefined → โหลด .glb/.gltf ไม่ได้เลย
  - เพิ่ม `wp3dmv-controls.js` (handle: `wp3dmv-controls`, dep: wp3dmv-three + wp3dmv-orbit)
    ก่อนหน้านี้ขาด → `WP3DMV_Controls` undefined → OrbitControls ไม่ทำงาน
  - เพิ่ม `wp3dmv-loader.js` (handle: `wp3dmv-loader`, dep: wp3dmv-three + wp3dmv-gltf)
    ก่อนหน้านี้ขาด → `WP3DMV_Loader` undefined → โหลด model ไม่ได้
  - อัปเดต deps ของ `wp3dmv-viewer` ให้ครบ:
    `[wp3dmv-three, wp3dmv-orbit, wp3dmv-gltf, wp3dmv-controls, wp3dmv-loader]`
    (เดิมมีแค่ three + orbit)
  - อัปเดต doc comment load order ให้ตรงกับ enqueue จริง (6 ขั้นตอน)

### Added (asset file)
- `assets/vendor/three/GLTFLoader.js` (ไฟล์ใหม่ — ต้องดาวน์โหลดจาก Three.js r158)
  - Source: https://raw.githubusercontent.com/mrdoob/three.js/r158/examples/js/loaders/GLTFLoader.js
  - ต้องวางที่ `assets/vendor/three/GLTFLoader.js` ก่อน deploy

---

## [1.7.0] — 2026-04-15 — Part 8: AJAX + Security

### Added
- `includes/class-wp3dmv-ajax.php` v1.0.0 (ใหม่)
  - Class `WP3DMV_AJAX` — จัดการ AJAX request ทั้งหมดของ plugin
  - `register_hooks()` — ลงทะเบียน `wp_ajax_wp3dmv_get_model` และ
    `wp_ajax_nopriv_wp3dmv_get_model` รองรับทั้ง logged-in และ guest
  - `get_model()` — handler หลัก: รับ attachment_id หรือ url จาก $_POST,
    ตรวจ nonce ด้วย `wp_verify_nonce()` (action: wp3dmv_nonce),
    sanitize ด้วย `absint()` / `sanitize_text_field()` / `wp_unslash()`,
    resolve URL จาก `wp_get_attachment_url()` ถ้ามี attachment_id,
    validate extension whitelist (.glb, .gltf) ผ่าน `is_valid_model_url()`,
    return ด้วย `esc_url_raw()` ก่อนส่ง JSON
  - `is_valid_model_url($url)` — private helper: ตรวจ extension ผ่าน
    `wp_parse_url()` + `pathinfo()` กัน query-string trick, whitelist จาก
    static property `$allowed_extensions`
  - `send_success($data)` — private wrapper: `wp_send_json_success()` + `die()`
  - `send_error($message, $code)` — private wrapper: `wp_send_json_error()` + `die()`
  - ไม่มี WooCommerce references ใดๆ
  - Security: nonce, sanitize, validate, esc_url_raw, explicit die() ทุก path

---

## [1.6.0] — 2026-04-15 — Part 7: Elementor Widget

### Added
- `elementor/class-wp3dmv-elementor.php` v1.0.0 (ใหม่)
  - `add_category($elements_manager)` — เพิ่ม category "WP3D" (slug: wp3dmv) ใน Elementor panel
  - `register_widgets($widgets_manager)` — require_once widget file แล้ว register WP3DMV_Widget_3D_Viewer
  - hooks: elementor/elements/categories_registered, elementor/widgets/register

- `elementor/widgets/class-widget-3d-viewer.php` v1.0.0 (ใหม่)
  - extends \Elementor\Widget_Base, guards ด้วย class_exists('Elementor\Widget_Base')
  - get_name() → 'wp3dmv-viewer', get_title() → '3D Model Viewer'
  - get_icon() → 'eicon-product-images', get_categories() → ['wp3dmv']
  - Content tab — Section Model: model_source (SELECT: upload/url),
    model_url (URL, condition: source=url), model_upload (MEDIA, condition: source=upload)
  - Content tab — Section Viewer Size: viewer_height (SLIDER 200–1000px default 400),
    viewer_width (SELECT: full/custom)
  - Content tab — Section Controls: auto_rotate (SWITCHER default yes),
    rotation_speed (SLIDER 0.1–5.0 default 1.0, condition: auto_rotate=yes),
    enable_zoom (SWITCHER default yes), show_hint (SWITCHER default yes)
  - Style tab — Section Style: bg_color (COLOR default #f5f5f5),
    border_radius (DIMENSIONS responsive, selector: .wp3dmv-container),
    box_shadow (GROUP_CONTROL_BOX_SHADOW, selector: .wp3dmv-container)
  - render() — get_settings_for_display(), resolve model URL, delegate WP3DMV_Viewer::render($args)
    ทำงานได้ทั้ง editor (is_edit_mode check) และ frontend
  - content_template() — Backbone.js template: placeholder เมื่อไม่มี URL,
    render skeleton (loading bar + canvas + hint) เมื่อมี URL
  - ไม่มี WooCommerce references ใดๆ

---

## [1.5.0] — 2026-04-15 — Part 6: Frontend CSS + Responsive + Mobile

### Added
- `public/css/wp3dmv-public.css` v1.0.0 (ใหม่)
  - CSS Variables (:root) — 20+ custom properties สำหรับสี, ขนาด, animation
    ให้ theme override ได้โดยไม่แก้ไฟล์ plugin (bg, loading, hint, btn, error, border-radius, box-shadow)
  - `.wp3dmv-container` — position: relative, width: 100%, overflow: hidden
    รับ height + bg-color จาก inline style ที่ PHP inject, รองรับ border-radius + box-shadow ผ่าน CSS vars
    มี Safari fix (-webkit-mask-image) สำหรับ border-radius clipping
  - `.wp3dmv-canvas` — width/height 100%, display: block, touch-action: none,
    cursor: grab / grabbing (via .is-dragging บน container)
  - `.wp3dmv-loading` — absolute overlay ครอบ container, flex centered,
    transition opacity+visibility, ซ่อนด้วย .hidden
  - `.wp3dmv-loading-bar` + inner `span` — progress bar แบบ track+fill,
    inner span รับ style="width: X%" จาก JS, transition 0.15s ease-out
  - `.wp3dmv-loading-text` — styled loading message
  - `.wp3dmv-controls-hint` — absolute bottom-center, pill shape,
    auto-fade animation หลัง 2.5s, ซ่อนด้วย .hidden
  - `.wp3dmv-fullscreen-btn` — absolute top-right, icon swap ด้วย
    .wp3dmv-icon-expand / .wp3dmv-icon-collapse,
    fullscreen state ผ่าน .is-fullscreen บน container (position: fixed)
  - `.wp3dmv-error` — hidden โดย default (display: none),
    แสดงด้วย .visible (display: flex), centered ใน container
  - Responsive (max-width: 767px) — hint text เล็กลง, fullscreen btn tap target ใหญ่ขึ้น
  - Touch device (@media pointer: coarse) — cursor: default แทน grab,
    hint fade delay สั้นลงเป็น 1.8s
  - Elementor context — .elementor-widget-wp3dmv-viewer รองรับ column layout,
    line-height: 0 ป้องกัน phantom gap, editor preview ปิด pointer-events canvas
  - ไม่มี WooCommerce class ใดๆ, ไม่พึ่ง framework ใดๆ (vanilla CSS)

---

## [1.4.0] — 2026-04-15 — Part 5: JavaScript Viewer (Three.js Core)

### Added
- `public/js/wp3dmv-viewer.js` v1.0.0
  - IIFE wrapper, exposes `window.WP3DMV_Viewer` namespace
  - `init(container)` — อ่าน data-model-url และ data-settings, สร้าง instance object
  - `createScene()` — THREE.Scene + scene.background จาก settings.bg_color
  - `createCamera()` — PerspectiveCamera FOV 45°, position Z = initial_camera_distance
  - `createRenderer()` — WebGLRenderer บน .wp3dmv-canvas, antialias: true, setPixelRatio, outputColorSpace = SRGBColorSpace
  - `createLights()` — AmbientLight 0.6 + DirectionalLight 0.8 ที่ (5,5,5)
  - `animate()` — requestAnimationFrame loop, controls.update(), renderer.render()
  - `onWindowResize()` — อัปเดต camera.aspect + renderer.setSize พร้อม zero-dimension guard
  - `initAll()` — วน querySelectorAll('.wp3dmv-container') แล้ว init() ทุก instance
  - Auto-initialize บน DOMContentLoaded, รองรับหลาย instance ในหน้าเดียวกัน

- `public/js/wp3dmv-controls.js` v1.0.0
  - IIFE wrapper, exposes `window.WP3DMV_Controls` namespace
  - `createControls(camera, canvas, settings)` — สร้าง THREE.OrbitControls
  - ตั้งค่าตาม §7.3: enableDamping, dampingFactor 0.05, minDistance 1, maxDistance 10
  - autoRotate / autoRotateSpeed รับจาก settings, enablePan: false
  - รองรับ mobile touch ผ

---

## [1.3.0] — 2026-04-15 — Part 4: Public Class + Viewer PHP + Template

### Added
- `public/class-wp3dmv-public.php` v1.0.0 (new)
  - `enqueue_styles()` — โหลด wp3dmv-public.css (handle: wp3dmv-public)
  - `enqueue_scripts()` — โหลด Three.js r158, OrbitControls, wp3dmv-viewer.js
    (handles: wp3dmv-three → wp3dmv-orbit → wp3dmv-viewer, in-footer)
  - `wp_localize_script()` ส่ง pluginUrl + settings ไปยัง JS
  - `register_shortcodes()` — ลงทะเบียน [wp3dmv_viewer]
  - `shortcode_viewer()` — delegate ไป WP3DMV_Viewer::render()

- `includes/class-wp3dmv-viewer.php` v1.0.0 (new)
  - `render($args)` static method — merge, sanitize, generate unique ID, include template
  - `get_global_settings()` — อ่าน wp3dmv_settings จาก wp_options พร้อม fallback
  - `merge_args()` — per-instance args override global defaults
  - `sanitize_settings()` — esc_url, absint, sanitize_hex_color, clamp floats
  - `parse_bool()` — รองรับ "true"/"false" string + boolean

- `public/partials/viewer-template.php` v1.0.0 (new)
  - HTML: .wp3dmv-container พร้อม data-model-url, data-settings (JSON)
  - Loading bar (.wp3dmv-loading-bar), loading text, error message (hidden)
  - Canvas (.wp3dmv-canvas) สำหรับ Three.js
  - Controls hint (drag/zoom), fullscreen button (conditional)
  - ทุก output ผ่าน esc_* ครบตาม Security Checklist
  - ไม่มี WooCommerce references ใดๆ

---

## [1.2.0] — 2026-04-14 — Part 3: Admin JS + Media Upload + CSS

### Added
- `admin/js/wp3dmv-admin.js` v1.0.0 (ใหม่)
  - `WP3DMV_Admin` object — admin controller หลัก
  - `initTabs()` — tab navigation พร้อม URL hash restore
  - `initColorPickers()` — เชื่อม WordPress iris color picker
  - `initRangeSliders()` — sync ค่า range input กับ label แบบ live
  - `initMediaUploadButtons()` — delegate ไปยัง `WP3DMV_MediaUpload`
  - `bindSaveNotice()` — auto-dismiss admin notice หลัง 3 วินาที

- `admin/js/wp3dmv-media-upload.js` v1.0.0 (ใหม่)
  - เปิด WordPress Media Library popup ด้วย `wp.media`
  - กรองเฉพาะไฟล์ `.glb` / `.gltf` ด้วย `_isValidModel()`
  - แสดง preview ชื่อไฟล์พร้อม icon badge (GLB / GLTF)
  - ปุ่ม Remove เพื่อล้างค่าและ reset UI
  - Custom events: `wp3dmv:model-selected`, `wp3dmv:model-removed`

- `admin/css/wp3dmv-admin.css` v1.0.0 (ใหม่)
  - CSS variables ผูกกับ `--wp-admin-theme-color` (รองรับทุก WP color scheme)
  - Styles สำหรับ Settings page: layout, tab nav, tab panels, form fields
  - Styles สำหรับ media upload widget: upload/remove buttons, file preview chip, error state
  - Responsive สำหรับ mobile admin (< 782px)


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
