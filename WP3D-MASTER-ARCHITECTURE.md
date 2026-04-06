# 🏗️ MASTER ARCHITECTURE
# WP 3D Model Viewer Plugin
**Version:** 1.0.0  
**Last Updated:** 2026-04-06  
**Compatible:** WordPress 6.x | WooCommerce 8.x | Elementor 3.x

---

## ⚠️ IMPORTANT RULES — อ่านก่อนทำทุกครั้ง!

### กฎสำหรับการพัฒนา
1. ✅ **อ่าน Master นี้ก่อนเริ่มทำงานทุกครั้ง**
2. ✅ **ใช้ชื่อไฟล์ / class / function ตาม Master เท่านั้น**
3. ✅ **เพิ่ม version header ทุกครั้งที่แก้ไฟล์**
4. ✅ **สรุป changelog หลังแก้เสร็จ และอัปเดตไฟล์ CHANGELOG.md (ห้ามเขียนทับ)**
5. ✅ **ถาม user ถ้าไม่แน่ใจเรื่อง version**
6. ✅ **🔴 กฎ Version Control (สำคัญมาก):**
   - **ก่อนแก้ไขไฟล์ใดๆ** → บอก user ว่าต้องการไฟล์ไหน → รอ user ส่งเวอร์ชันล่าสุดมาก่อน
   - **ห้ามอ้างอิงไฟล์จาก context/memory** ของ Claude เพราะอาจเป็นเวอร์ชันเก่า
   - **ถ้าสร้างไฟล์ใหม่ทั้งหมด** → ไม่ต้องขอ (ไม่มี version conflict)
   - **ถ้าแก้ไขไฟล์ที่มีอยู่** → ต้องขอเวอร์ชันล่าสุดจาก user ก่อนเสมอ
   - เหตุผล: หลาย Chat ทำงานแยกกัน → ไฟล์อาจถูกแก้ใน Chat อื่นแล้ว → Claude ไม่รู้

### เมื่อเริ่ม Chat ใหม่
```
1. บอก Claude: "อ่าน Master Architecture ก่อน"
2. ระบุว่าจะทำงานไฟล์ไหน (ดู Section 9: Chat Splitting Guide)
3. ตรวจสอบ version ปัจจุบันจาก Master
4. 🔴 ถ้าจะแก้ไขไฟล์ที่มีอยู่ → บอก user ว่าต้องการไฟล์ไหน → รอรับก่อนเริ่ม
5. จบ Chat → สรุป changelog สำหรับอัปเดต Master
```

---

## 1. PROJECT OVERVIEW

| Item | Detail |
|------|--------|
| Plugin Name | WP 3D Model Viewer |
| Plugin Slug | `wp-3d-model-viewer` |
| Text Domain | `wp3dmv` |
| Main File | `wp-3d-model-viewer.php` |
| Min PHP | 7.4 |
| Min WP | 6.0 |
| License | GPL-2.0+ |

### วัตถุประสงค์
แสดง 3D Model แบบ interactive บน product page ของ WooCommerce และ Elementor widget รองรับการหมุน 360° ด้วย mouse/touch drag.

### Supported 3D Formats
- `.glb` — GL Transmission Format Binary (หลัก)
- `.gltf` — GL Transmission Format (พร้อม textures)
- `.obj` — Wavefront OBJ (optional future)

---

## 2. TECHNOLOGY STACK

| Layer | Technology | Reason |
|-------|-----------|--------|
| 3D Renderer | **Three.js r158** | Industry standard, lightweight |
| Orbit Control | `OrbitControls.js` (Three.js addon) | Mouse/touch drag หมุน 360° |
| WP Integration | WordPress Plugin API | Standard hooks/filters |
| WooCommerce | WC Product Meta + Gallery Hook | เชื่อม model กับ product |
| Elementor | Elementor Widget API | Drag & drop widget |
| Admin UI | Vanilla JS + WP Media Library | จัดการ upload model |
| Build Tool | None (vanilla, no bundler) | ง่าย deploy บน shared hosting |

---

## 3. FILE STRUCTURE (Complete)

```
wp-3d-model-viewer/
│
├── 📄 wp-3d-model-viewer.php          # Main plugin file (v1.0.0)
├── 📄 uninstall.php                    # Cleanup on uninstall (v1.0.0)
├── 📄 readme.txt                       # WP.org readme
│
├── 📁 includes/                        # Core PHP classes
│   ├── class-wp3dmv-loader.php         # Hook loader (v1.0.0)
│   ├── class-wp3dmv-activator.php      # Activation tasks (v1.0.0)
│   ├── class-wp3dmv-deactivator.php    # Deactivation tasks (v1.0.0)
│   ├── class-wp3dmv-i18n.php           # Internationalization (v1.0.0)
│   ├── class-wp3dmv-core.php           # Main plugin class (v1.0.0)
│   ├── class-wp3dmv-viewer.php         # Viewer render logic (v1.0.0)
│   └── class-wp3dmv-ajax.php           # AJAX handlers (v1.0.0)
│
├── 📁 admin/                           # Admin side
│   ├── class-wp3dmv-admin.php          # Admin main class (v1.0.0)
│   ├── class-wp3dmv-settings.php       # Settings page (v1.0.0)
│   ├── class-wp3dmv-product-meta.php   # WooCommerce product metabox (v1.0.0)
│   ├── 📁 js/
│   │   ├── wp3dmv-admin.js             # Admin scripts (v1.0.0)
│   │   └── wp3dmv-media-upload.js      # WP Media Library upload (v1.0.0)
│   └── 📁 css/
│       └── wp3dmv-admin.css            # Admin styles (v1.0.0)
│
├── 📁 public/                          # Frontend side
│   ├── class-wp3dmv-public.php         # Public main class (v1.0.0)
│   ├── 📁 js/
│   │   ├── wp3dmv-viewer.js            # Main viewer controller (v1.0.0)
│   │   ├── wp3dmv-controls.js          # Mouse/touch orbit controls (v1.0.0)
│   │   └── wp3dmv-loader.js            # GLB/GLTF file loader (v1.0.0)
│   ├── 📁 css/
│   │   └── wp3dmv-public.css           # Frontend styles (v1.0.0)
│   └── 📁 partials/
│       └── viewer-template.php         # HTML template for viewer (v1.0.0)
│
├── 📁 elementor/                       # Elementor integration
│   ├── class-wp3dmv-elementor.php      # Elementor manager (v1.0.0)
│   └── widgets/
│       └── class-widget-3d-viewer.php  # Elementor widget (v1.0.0)
│
├── 📁 woocommerce/                     # WooCommerce integration
│   ├── class-wp3dmv-woocommerce.php    # WC integration class (v1.0.0)
│   └── templates/
│       └── single-product-3d.php       # Product page template (v1.0.0)
│
├── 📁 assets/                          # Static assets
│   ├── 📁 vendor/
│   │   └── three/
│   │       ├── three.min.js            # Three.js r158
│   │       └── OrbitControls.js        # Orbit controls
│   ├── 📁 images/
│   │   └── placeholder-3d.png          # Placeholder image
│   └── 📁 models/
│       └── sample.glb                  # Sample model for demo
│
├── 📁 languages/                       # Translation files
│   └── wp3dmv-th.po/.mo               # Thai language
│
└── 📄 CHANGELOG.md                     # ← อัปเดตทุกครั้งที่แก้ไข
```

---

## 4. DATABASE SCHEMA

### WordPress Options Table
```
wp_options:
  option_name: wp3dmv_settings
  option_value: {
    "default_bg_color": "#f5f5f5",
    "default_auto_rotate": true,
    "default_rotation_speed": 1.0,
    "enable_zoom": true,
    "enable_fullscreen": true,
    "loading_text": "กำลังโหลด...",
    "cache_duration": 3600
  }
```

### WordPress Post Meta (WooCommerce Product)
```
wp_postmeta:
  meta_key: _wp3dmv_model_url        # URL ของ .glb file
  meta_key: _wp3dmv_model_id         # attachment ID ใน Media Library
  meta_key: _wp3dmv_viewer_settings  # JSON settings เฉพาะ product นี้
  meta_key: _wp3dmv_enabled          # "yes" / "no"
  meta_key: _wp3dmv_position         # "replace_gallery" / "below_gallery" / "tab"
```

---

## 5. CORE CLASS ARCHITECTURE

### 5.1 Main Plugin File — `wp-3d-model-viewer.php`
```php
// File version header (บังคับทุกไฟล์)
/**
 * Plugin Name: WP 3D Model Viewer
 * Version: 1.0.0
 * @package WP3DModelViewer
 * @version 1.0.0
 */

define('WP3DMV_VERSION', '1.0.0');
define('WP3DMV_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP3DMV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP3DMV_PLUGIN_FILE', __FILE__);
```

### 5.2 Class Naming Convention
| Class | File | Responsibility |
|-------|------|----------------|
| `WP3DMV_Core` | `class-wp3dmv-core.php` | Bootstrap, load all modules |
| `WP3DMV_Admin` | `class-wp3dmv-admin.php` | Admin menus, pages |
| `WP3DMV_Settings` | `class-wp3dmv-settings.php` | Plugin settings page |
| `WP3DMV_Product_Meta` | `class-wp3dmv-product-meta.php` | WC product metabox |
| `WP3DMV_Public` | `class-wp3dmv-public.php` | Enqueue scripts, shortcodes |
| `WP3DMV_Viewer` | `class-wp3dmv-viewer.php` | Generate viewer HTML |
| `WP3DMV_WooCommerce` | `class-wp3dmv-woocommerce.php` | WC hooks |
| `WP3DMV_Elementor` | `class-wp3dmv-elementor.php` | Register Elementor widget |
| `WP3DMV_Widget_3D_Viewer` | `class-widget-3d-viewer.php` | Elementor widget class |
| `WP3DMV_AJAX` | `class-wp3dmv-ajax.php` | AJAX handlers |
| `WP3DMV_Loader` | `class-wp3dmv-loader.php` | Hook registration |
| `WP3DMV_Activator` | `class-wp3dmv-activator.php` | Install/upgrade |

---

## 6. HOOKS & FILTERS MAP

### WordPress Hooks
```
init                    → WP3DMV_Core::init()
plugins_loaded          → WP3DMV_Core::load_textdomain()
wp_enqueue_scripts      → WP3DMV_Public::enqueue_scripts()
admin_enqueue_scripts   → WP3DMV_Admin::enqueue_scripts()
```

### WooCommerce Hooks
```
add_meta_boxes                          → WP3DMV_Product_Meta::add_metabox()
woocommerce_process_product_meta        → WP3DMV_Product_Meta::save_meta()
woocommerce_before_single_product_summary → WP3DMV_WooCommerce::render_viewer() [position A]
woocommerce_after_single_product_summary  → WP3DMV_WooCommerce::render_viewer() [position B]
woocommerce_product_tabs                  → WP3DMV_WooCommerce::add_3d_tab()     [position C]
```

### Shortcodes
```
[wp3dmv_viewer id="POST_ID"]           # แสดง viewer จาก product/post ID
[wp3dmv_viewer url="MODEL_URL"]        # แสดง viewer จาก URL โดยตรง
[wp3dmv_viewer id="123" height="500" bg="#fff" autorotate="true"]
```

### Elementor
```
elementor/widgets/register  → WP3DMV_Elementor::register_widgets()
elementor/elements/categories_registered → WP3DMV_Elementor::add_category()
```

---

## 7. JAVASCRIPT ARCHITECTURE

### 7.1 Viewer Initialization Flow
```
DOM Ready
  └─ WP3DMV_Viewer.init(container)
       ├─ createScene()         # THREE.Scene
       ├─ createCamera()        # THREE.PerspectiveCamera
       ├─ createRenderer()      # THREE.WebGLRenderer (canvas)
       ├─ createLights()        # AmbientLight + DirectionalLight
       ├─ createControls()      # OrbitControls (mouse drag)
       ├─ loadModel(url)        # GLTFLoader → scene.add(model)
       │    └─ onProgress(xhr)  # Loading bar %
       └─ animate()             # requestAnimationFrame loop
```

### 7.2 HTML Output (viewer-template.php)
```html
<div class="wp3dmv-container" 
     data-model-url="<?= esc_url($model_url) ?>"
     data-settings='<?= esc_attr(json_encode($settings)) ?>'
     id="wp3dmv-<?= $unique_id ?>">
  
  <div class="wp3dmv-loading">
    <div class="wp3dmv-loading-bar"><span></span></div>
    <p class="wp3dmv-loading-text">กำลังโหลด...</p>
  </div>
  
  <canvas class="wp3dmv-canvas"></canvas>
  
  <div class="wp3dmv-controls-hint">
    <span>🖱 ลาก เพื่อหมุน</span>
    <span>🔍 Scroll เพื่อซูม</span>
  </div>
  
</div>
```

### 7.3 OrbitControls Settings (Default)
```javascript
controls.enableDamping = true;      // smooth movement
controls.dampingFactor = 0.05;
controls.enableZoom = true;
controls.minDistance = 1;
controls.maxDistance = 10;
controls.autoRotate = settings.autoRotate;
controls.autoRotateSpeed = settings.rotationSpeed;
controls.enablePan = false;         // ปิด pan (เดิน)
```

---

## 8. SETTINGS PAGE SCHEMA

```
Admin → WP 3D Model Viewer → Settings

[General]
- Default Background Color     : colorpicker  (#f5f5f5)
- Default Height               : number (px)  (400)
- Show Controls Hint           : checkbox     (true)
- Enable Fullscreen Button     : checkbox     (true)

[3D Viewer Defaults]
- Auto Rotate                  : checkbox     (true)
- Rotation Speed               : slider 0.1–5 (1.0)
- Enable Zoom                  : checkbox     (true)
- Initial Camera Distance      : number       (3)

[WooCommerce]
- Default Position             : select
    [ replace_gallery | below_gallery | in_tab ]
- Tab Label                    : text         ("ดู 3D")

[Performance]
- Lazy Load                    : checkbox     (true)
- Max Texture Size             : select       (1024 / 2048 / 4096)
```

---

## 9. CHAT SPLITTING GUIDE

> แบ่งงานเป็น Chat ย่อย เพื่อไม่ให้ context เต็ม

| Chat # | งาน | ไฟล์ที่เกี่ยวข้อง |
|--------|-----|-----------------|
| Chat 1 | Main plugin file + Core class + Activator | `wp-3d-model-viewer.php`, `class-wp3dmv-core.php`, `class-wp3dmv-activator.php`, `class-wp3dmv-loader.php` |
| Chat 2 | Admin + Settings + Product Metabox | `class-wp3dmv-admin.php`, `class-wp3dmv-settings.php`, `class-wp3dmv-product-meta.php` |
| Chat 3 | Admin JS + Media Upload | `wp3dmv-admin.js`, `wp3dmv-media-upload.js`, `wp3dmv-admin.css` |
| Chat 4 | Public class + Viewer PHP + Template | `class-wp3dmv-public.php`, `class-wp3dmv-viewer.php`, `viewer-template.php` |
| Chat 5 | **JavaScript Viewer** (Three.js core) | `wp3dmv-viewer.js`, `wp3dmv-controls.js`, `wp3dmv-loader.js` |
| Chat 6 | CSS + Responsive + Mobile touch | `wp3dmv-public.css` |
| Chat 7 | WooCommerce integration | `class-wp3dmv-woocommerce.php`, `single-product-3d.php` |
| Chat 8 | Elementor Widget | `class-wp3dmv-elementor.php`, `class-widget-3d-viewer.php` |
| Chat 9 | AJAX + Security | `class-wp3dmv-ajax.php` |
| Chat 10 | Testing + Bugfix | ทุกไฟล์ |

---

## 10. VERSION TRACKING TABLE

> อัปเดตทุกครั้งที่มีการแก้ไขไฟล์

| File | Current Version | Last Modified | Modified In Chat |
|------|----------------|---------------|-----------------|
| `wp-3d-model-viewer.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-core.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-admin.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-settings.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-product-meta.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-public.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-viewer.php` | 1.0.0 | 2026-04-06 | Initial |
| `wp3dmv-viewer.js` | 1.0.0 | 2026-04-06 | Initial |
| `wp3dmv-controls.js` | 1.0.0 | 2026-04-06 | Initial |
| `wp3dmv-loader.js` | 1.0.0 | 2026-04-06 | Initial |
| `wp3dmv-public.css` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-woocommerce.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-elementor.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-widget-3d-viewer.php` | 1.0.0 | 2026-04-06 | Initial |
| `class-wp3dmv-ajax.php` | 1.0.0 | 2026-04-06 | Initial |

---

## 11. SECURITY CHECKLIST

- [ ] Nonce verification ทุก form submit และ AJAX call
- [ ] `sanitize_text_field()` ทุก input
- [ ] `esc_url()` ทุก URL output
- [ ] `esc_attr()` ทุก HTML attribute output
- [ ] `wp_verify_nonce()` ก่อน save ทุกครั้ง
- [ ] Capability check (`manage_options` / `edit_products`)
- [ ] File type validation (`.glb`, `.gltf` เท่านั้น)
- [ ] File size limit ใน upload

---

## 12. ELEMENTOR WIDGET CONTROLS

```
Widget Name: "3D Model Viewer"
Category: "WP3D"

Controls:
├─ Section: Model
│   ├─ model_source   : SELECT (upload / url / product)
│   ├─ model_url      : URL input
│   └─ product_id     : NUMBER (ใช้เมื่อ source=product)
│
├─ Section: Viewer Size
│   ├─ viewer_height  : SLIDER (200–1000px, default 400)
│   └─ viewer_width   : SELECT (full / custom %)
│
├─ Section: Controls
│   ├─ auto_rotate    : SWITCHER
│   ├─ rotation_speed : SLIDER
│   ├─ enable_zoom    : SWITCHER
│   └─ show_hint      : SWITCHER
│
└─ Section: Style
    ├─ bg_color       : COLOR
    ├─ border_radius  : DIMENSIONS
    └─ box_shadow     : BOX SHADOW
```

---

## 13. DEVELOPMENT PHASES

### Phase 1 — Core (Chat 1–3)
- [x] Architecture planning ✅
- [ ] Plugin bootstrap + activation
- [ ] Admin settings page
- [ ] Product metabox (WooCommerce)

### Phase 2 — Viewer (Chat 4–6)
- [ ] PHP render class
- [ ] Three.js viewer (JavaScript core)
- [ ] Mouse/touch orbit controls
- [ ] Loading bar + error handling
- [ ] Responsive CSS

### Phase 3 — Integration (Chat 7–8)
- [ ] WooCommerce product gallery integration
- [ ] Elementor widget

### Phase 4 — Polish (Chat 9–10)
- [ ] AJAX endpoints
- [ ] Security hardening
- [ ] Performance optimization
- [ ] Cross-browser testing

---

## 14. KNOWN CONSTRAINTS & DECISIONS

| Issue | Decision | Reason |
|-------|----------|--------|
| No bundler (webpack/vite) | ✅ ใช้ vanilla JS | Deploy ง่าย บน shared hosting |
| Three.js version | r158 fixed | ไม่ upgrade กลางโปรเจกต์ |
| Mobile support | OrbitControls touch events | built-in รองรับแล้ว |
| Large GLB files | Lazy load + loading bar | UX ดีกว่า block render |
| Elementor widget reload | Use `elementor/frontend/init` hook | ป้องกัน conflict |

---

*Master Architecture v1.0.0 — สร้างโดย Claude, 2026-04-06*
