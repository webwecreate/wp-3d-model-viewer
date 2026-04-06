# 📘 Git Workflow Guide
# สำหรับ WP 3D Model Viewer Plugin Development
**Version:** 1.0.0 | สำหรับผู้เริ่มต้น Git

---

## 🤔 Git คืออะไร? ทำไมต้องใช้?

Git คือระบบ **Version Control** — เหมือนมี "เครื่องย้อนเวลา" สำหรับโค้ด

```
ก่อนมี Git:
  plugin_v1.php
  plugin_v2.php
  plugin_v2_final.php
  plugin_v2_final_REAL.php   ← สับสนมาก!

หลังมี Git:
  plugin.php  (Git จำทุก version ให้โดยอัตโนมัติ)
```

**ประโยชน์:**
- ✅ ย้อนกลับไปเวอร์ชันเก่าได้ทุกเมื่อ
- ✅ รู้ว่าใครแก้อะไร เมื่อไหร่
- ✅ ทำงานหลาย feature พร้อมกันได้ (branches)
- ✅ Backup อัตโนมัติบน GitHub/GitLab

---

## 🛠️ STEP 1 — ติดตั้ง Git

### Windows
1. ไปที่ https://git-scm.com/download/win
2. Download และ install (กด Next ไปเรื่อยๆ ได้เลย)
3. เปิด **Git Bash** (ติดมากับ Git)

### Mac
```bash
# เปิด Terminal แล้วพิมพ์:
xcode-select --install
# หรือ ถ้ามี Homebrew:
brew install git
```

### ตรวจสอบว่าติดตั้งสำเร็จ
```bash
git --version
# ควรเห็น: git version 2.xx.x
```

---

## 🔧 STEP 2 — ตั้งค่า Git ครั้งแรก (ทำครั้งเดียว)

```bash
# ตั้งชื่อและอีเมล (จะติดใน commit ทุกอัน)
git config --global user.name "Your Name"
git config --global user.email "your@email.com"

# ตั้ง default branch เป็น main
git config --global init.defaultBranch main

# ตรวจสอบ
git config --list
```

---

## ☁️ STEP 3 — สร้าง GitHub Account

1. ไปที่ **https://github.com**
2. กด **Sign up** → ใส่ email, username, password
3. Verify email
4. Done!

> GitHub = ที่เก็บ Git ออนไลน์ (เหมือน Google Drive แต่สำหรับโค้ด)

---

## 📁 STEP 4 — สร้าง Repository สำหรับ Plugin

### บน GitHub (Remote)
1. Login GitHub
2. กด **"+"** (มุมขวาบน) → **New repository**
3. ตั้งค่า:
   ```
   Repository name: wp-3d-model-viewer
   Description: WordPress 3D Model Viewer Plugin
   Visibility: Private (แนะนำ สำหรับ plugin เชิงพาณิชย์)
   ☑ Add a README file
   .gitignore template: WordPress   ← สำคัญมาก!
   License: GPL-2.0
   ```
4. กด **Create repository**

### บน Local (เครื่องของเรา)
```bash
# ไปที่ folder ที่เก็บ plugin (เช่น wp-content/plugins/)
cd /path/to/wp-content/plugins/

# Clone repository จาก GitHub มาที่เครื่อง
git clone https://github.com/YOUR_USERNAME/wp-3d-model-viewer.git

# เข้าไปใน folder
cd wp-3d-model-viewer

# ดู status ปัจจุบัน
git status
```

---

## 📝 STEP 5 — .gitignore สำหรับ WordPress Plugin

สร้างไฟล์ `.gitignore` ใน root ของ plugin:

```gitignore
# WordPress
wp-config.php
wp-content/uploads/
wp-content/cache/

# Plugin specific
*.log
*.DS_Store
Thumbs.db

# Development
node_modules/
.env
.env.local

# IDE
.idea/
.vscode/
*.suo
*.ntvs*

# Build artifacts
/dist/
/build/

# Composer
/vendor/
composer.lock
```

---

## 🌿 STEP 6 — Branch Strategy

> Branch = "ทางแยก" ของโค้ด ให้ทำงาน feature ต่างๆ แยกกัน

### โครงสร้าง Branch ของโปรเจกต์นี้

```
main ─────────────────────────────────────► (Production, stable)
  │
  ├─ develop ───────────────────────────── (Development, รวม features)
  │     │
  │     ├─ feature/core-plugin ──────────── (Chat 1-3: Core files)
  │     ├─ feature/js-viewer ─────────────── (Chat 4-6: Three.js viewer)
  │     ├─ feature/woocommerce ──────────── (Chat 7: WC integration)
  │     └─ feature/elementor ────────────── (Chat 8: Elementor widget)
  │
  └─ hotfix/xxx ─────────────────────────── (แก้ bug เร่งด่วน)
```

### คำสั่ง Branch

```bash
# ดู branch ทั้งหมด
git branch -a

# สร้าง branch ใหม่และสลับไป
git checkout -b feature/core-plugin

# สลับ branch
git checkout develop

# ลบ branch (หลัง merge เสร็จ)
git branch -d feature/core-plugin
```

---

## 💾 STEP 7 — Workflow ประจำวัน (Daily Workflow)

### ทุกครั้งที่เริ่มทำงาน
```bash
# 1. ดึงโค้ดล่าสุดจาก GitHub
git pull origin develop

# 2. สร้าง/สลับไป feature branch
git checkout -b feature/core-plugin
# หรือถ้ามีอยู่แล้ว:
git checkout feature/core-plugin
```

### ระหว่างทำงาน (บ่อยๆ)
```bash
# ดูว่าไฟล์ไหนเปลี่ยนแปลงบ้าง
git status

# ดูรายละเอียดที่เปลี่ยน
git diff

# เพิ่มไฟล์เข้า staging area
git add wp-3d-model-viewer.php        # เพิ่มไฟล์เดียว
git add includes/                      # เพิ่มทั้ง folder
git add .                              # เพิ่มทุกอย่าง (ระวัง!)

# Commit พร้อม message
git commit -m "feat: add main plugin bootstrap file v1.0.0"
```

### เมื่อทำ Feature เสร็จ
```bash
# Push ขึ้น GitHub
git push origin feature/core-plugin

# บน GitHub: สร้าง Pull Request → merge เข้า develop
```

---

## 📋 STEP 8 — Commit Message Convention

> ใช้รูปแบบนี้ทุก commit เพื่อให้ history อ่านง่าย

```
<type>: <short description> (v<version>)

Types:
  feat     → เพิ่ม feature ใหม่
  fix      → แก้ bug
  docs     → แก้ documentation
  style    → แก้ CSS/formatting (ไม่กระทบ logic)
  refactor → refactor code
  test     → เพิ่ม/แก้ tests
  chore    → งานซ่อมบำรุง (update dependencies ฯลฯ)
```

### ตัวอย่าง Good Commit Messages
```bash
git commit -m "feat: add main plugin file with WP3DMV_VERSION constant v1.0.0"
git commit -m "feat: add product metabox for GLB model upload v1.0.0"
git commit -m "fix: fix OrbitControls not responding on mobile touch v1.0.1"
git commit -m "feat: add Elementor 3D viewer widget v1.0.0"
git commit -m "docs: update CHANGELOG.md for v1.0.1"
git commit -m "style: fix viewer container responsive CSS v1.0.1"
```

### Bad Commit Messages ❌
```bash
git commit -m "fix"           # ไม่รู้แก้อะไร
git commit -m "update"        # ไม่รู้อัปเดตอะไร
git commit -m "asdfgh"        # ไม่มีความหมาย
```

---

## 🔄 STEP 9 — Workflow กับ Claude (Multi-Chat)

เนื่องจากเราแบ่งงานเป็นหลาย Chat และแต่ละ Chat ทำงานกับไฟล์คนละชุด:

```
┌─────────────────────────────────────────────────┐
│              WORKFLOW ต่อ Chat                   │
│                                                 │
│  Claude สร้าง/แก้ไฟล์                           │
│       ↓                                         │
│  Copy โค้ดจาก Claude                            │
│       ↓                                         │
│  วางในไฟล์บน Local                               │
│       ↓                                         │
│  git add <files>                                │
│  git commit -m "feat: ..."                      │
│  git push origin feature/xxx                    │
│       ↓                                         │
│  Chat ต่อไป: git pull ก่อนเริ่มทำงาน            │
└─────────────────────────────────────────────────┘
```

### ก่อน Chat ใหม่กับ Claude
```bash
# 1. Pull โค้ดล่าสุด
git pull origin develop

# 2. ดู version ของไฟล์ที่จะแก้
# (ดูจาก version header ในไฟล์ หรือ Section 10 ใน Master Architecture)

# 3. ส่ง Master Architecture + ไฟล์ล่าสุดให้ Claude
```

### หลัง Chat กับ Claude เสร็จ
```bash
# 1. บันทึกไฟล์ที่แก้แล้ว
git add .

# 2. Commit พร้อม message ชัดเจน
git commit -m "feat: add Three.js viewer core with OrbitControls v1.0.0"

# 3. Push ขึ้น GitHub
git push origin feature/js-viewer

# 4. อัปเดต CHANGELOG.md และ Master Architecture
git add CHANGELOG.md WP3D-MASTER-ARCHITECTURE.md
git commit -m "docs: update changelog and master arch for Chat 5"
git push origin feature/js-viewer
```

---

## 🏷️ STEP 10 — Tags & Releases (เมื่อ Plugin พร้อม)

```bash
# สร้าง tag สำหรับ release
git tag -a v1.0.0 -m "Release v1.0.0 - Initial stable release"

# Push tag ขึ้น GitHub
git push origin v1.0.0

# Push ทุก tags พร้อมกัน
git push origin --tags
```

---

## 🆘 STEP 11 — คำสั่ง Emergency (กู้คืน)

```bash
# ย้อนกลับไปก่อนหน้า commit ล่าสุด (ยังไม่ push)
git reset --soft HEAD~1

# ย้อนกลับไฟล์ที่แก้ (ยังไม่ commit)
git checkout -- <filename>

# ดู history ทั้งหมด
git log --oneline

# กลับไปยัง commit ที่ต้องการ (สร้าง branch ใหม่)
git checkout -b hotfix/revert abc1234

# ดูว่าไฟล์เปลี่ยนอะไรระหว่าง commits
git diff abc1234 def5678 -- includes/class-wp3dmv-viewer.php
```

---

## 📊 STEP 12 — สรุปคำสั่งที่ใช้บ่อย

| คำสั่ง | ความหมาย |
|--------|---------|
| `git status` | ดูสถานะไฟล์ |
| `git add .` | เพิ่มทุกไฟล์ใน staging |
| `git commit -m "msg"` | บันทึก snapshot |
| `git push origin <branch>` | ส่งขึ้น GitHub |
| `git pull origin <branch>` | ดึงจาก GitHub |
| `git checkout -b <branch>` | สร้าง + สลับ branch |
| `git log --oneline` | ดู history สั้นๆ |
| `git diff` | ดูสิ่งที่เปลี่ยนแปลง |
| `git stash` | ซ่อนงานชั่วคราว |
| `git stash pop` | เอางานที่ซ่อนกลับมา |

---

## 🗂️ STEP 13 — โครงสร้าง GitHub Repository

```
wp-3d-model-viewer (GitHub)
│
├── 📋 README.md          ← อธิบาย plugin (Auto-generated)
├── 📄 .gitignore          ← ไฟล์ที่ Git ไม่ track
├── 📄 LICENSE             ← GPL-2.0
│
├── 🌿 main               ← Production (stable releases)
├── 🌿 develop            ← Development (รวม features)
├── 🌿 feature/core-plugin
├── 🌿 feature/js-viewer
├── 🌿 feature/woocommerce
└── 🌿 feature/elementor
```

---

## ✅ Checklist ก่อนเริ่มใช้ Git

- [ ] ติดตั้ง Git บนเครื่อง
- [ ] ตั้งค่า user.name และ user.email
- [ ] สร้าง GitHub account
- [ ] สร้าง repository `wp-3d-model-viewer` (Private)
- [ ] Clone มายัง local
- [ ] สร้าง `.gitignore`
- [ ] สร้าง branch `develop`
- [ ] ทดสอบ push ครั้งแรก

---

*Git Workflow Guide v1.0.0 — สร้างโดย Claude, 2026-04-06*
