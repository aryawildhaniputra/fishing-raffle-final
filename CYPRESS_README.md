# Cypress Testing untuk Fishing Raffle - Lottery Drawing

Struktur testing Cypress untuk menguji sistem pengundian (lottery drawing) pada aplikasi Fishing Raffle.

## 📁 Struktur File

```
fishing-raffle/
├── cypress/
│   ├── e2e/
│   │   ├── event1-group-2.cy.js           # Test Event 1: Grup 2 orang (111 grup)
│   │   ├── event2-group-3.cy.js           # Test Event 2: Grup 3 orang (74 grup)
│   │   ├── event3-mixed-groups.cy.js      # Test Event 3: Campuran 1-5 (44-222 grup)
│   │   ├── event4-random-1.cy.js          # Test Event 4: Random 2-5 (44-111 grup)
│   │   └── event5-random-2.cy.js          # Test Event 5: Random 2-5 (44-111 grup)
│   ├── support/
│   │   ├── commands.js                    # Custom Cypress commands
│   │   └── e2e.js                         # Support file
│   └── fixtures/                          # (optional) Test data
├── cypress.config.js                      # Konfigurasi Cypress
└── package.json
```

## 🚀 Cara Menjalankan

### 1. Persiapan Database

Pastikan database sudah di-seed dengan 5 event:

```bash
php artisan migrate:fresh --seed
```

### 2. Jalankan Laravel Server

```bash
php artisan serve
```

Server akan berjalan di `http://127.0.0.1:8000`

### 3. Jalankan Cypress

**Mode Interactive (GUI):**
```bash
npx cypress open
```

**Mode Headless (CI/CD):**
```bash
npx cypress run
```

**Run specific test:**
```bash
npx cypress run --spec "cypress/e2e/event1-group-2.cy.js"

# Or using npm scripts
npm run test:event1  # Event 1: 111 groups
npm run test:event2  # Event 2: 74 groups
npm run test:event3  # Event 3: Mixed groups
npm run test:event4  # Event 4: Random groups
npm run test:event5  # Event 5: Random groups
npm run test:all     # Run ALL test files
```

## 📋 Test Files

### 1. `event1-group-2.cy.js`
Test untuk Event 1 dengan semua grup berisi 2 orang.
**Total: 111 grup (222 members ÷ 2)**

**Test cases:**
- ✅ Navigate to event detail page
- ✅ Navigate to Pengundian tab
- ✅ **Draw ALL 111 groups**
- ✅ Test redraw functionality

### 2. `event2-group-3.cy.js`
Test untuk Event 2 dengan semua grup berisi 3 orang.
**Total: 74 grup (222 members ÷ 3)**

**Test cases:**
- ✅ **Draw ALL 74 groups**

### 3. `event3-mixed-groups.cy.js`
Test untuk Event 3 dengan grup campuran 1-5 orang.
**Total: 44-222 grup (bervariasi)**

**Test cases:**
- ✅ **Draw ALL groups** (mixed 1-5 members)
- ✅ Verify number variety (adaptive logic test)

### 4. `event4-random-1.cy.js`
Test untuk Event 4 dengan grup random 2-5 orang.
**Total: 44-111 grup (bervariasi)**

**Test cases:**
- ✅ **Draw ALL groups**
- ✅ Verify number distribution

### 5. `event5-random-2.cy.js`
Test untuk Event 5 dengan grup random 2-5 orang.
**Total: 44-111 grup (bervariasi)**

**Test cases:**
- ✅ **Draw ALL groups**
- ✅ Verify number distribution

## 🎯 Custom Commands

File `cypress/support/commands.js` berisi custom commands:

### Login
```javascript
cy.login() // Default: farhan12 / adminpass
cy.login('farhan12', 'adminpass')
```

### Modal Handling
```javascript
cy.waitForModal('drawModal')
cy.clickDrawButton()
cy.waitForNumberAnimation()
```

### Lottery Actions
```javascript
cy.selectStallPosition('upper')  // Pilih ATAS
cy.selectStallPosition('lower')  // Pilih BAWAH
cy.redrawLottery()               // Undi ulang
```

## 📊 Test Scenarios

### Scenario 1: Basic Draw Flow
1. Login sebagai admin
2. Navigate ke Events
3. Pilih event
4. Klik tab Pengundian
5. Klik tombol MULAI UNDIAN
6. Wait for number animation
7. Pilih ATAS atau BAWAH
8. Verify success

### Scenario 2: Multiple Draws
Loop untuk melakukan beberapa undian berturut-turut dan collect statistics.

### Scenario 3: Redraw Testing
1. Klik MULAI UNDIAN
2. Get first number
3. Klik Undi Ulang
4. Get second number
5. Verify numbers might be different

### Scenario 4: Adaptive Logic Verification
1. Perform 20+ draws
2. Collect all drawn numbers
3. Analyze distribution by ranges
4. Verify variety (middle numbers should appear)

## 🔍 Verifikasi

### Adaptive Logic Test
Test `all-events-comprehensive.cy.js` akan menganalisis distribusi angka:

```
=== Number Distribution Analysis ===
1-50: 5 (25.0%)
51-100: 4 (20.0%)
101-150: 6 (30.0%)
151-200: 3 (15.0%)
201-222: 2 (10.0%)

Has middle range numbers (50-170): true
✓ Adaptive logic is working - variety detected!
```

## 📸 Screenshots

Cypress akan otomatis mengambil screenshot:
- `event1-first-draw-number.png`
- `event1-after-5-draws.png`
- `event2-group-3-draw.png`
- `event3-mixed-groups-variety.png`
- `all-events-1-Event-Test-Grup-2-Orang.png`
- dll.

Screenshots disimpan di `cypress/screenshots/`

## 🎥 Videos

Cypress akan merekam video test run di `cypress/videos/`

## ⚙️ Configuration

File `cypress.config.js`:

```javascript
{
  baseUrl: 'http://127.0.0.1:8000',
  viewportWidth: 1920,
  viewportHeight: 1080,
  video: true,
  screenshotOnRunFailure: true
}
```

## 🐛 Troubleshooting

### Test gagal karena timeout
Increase timeout di `cypress.config.js`:
```javascript
defaultCommandTimeout: 10000
```

### Modal tidak muncul
Check apakah class `.btn-draw` ada dan visible.

### Number tidak ter-load
Increase wait time di `waitForNumberAnimation()` command.

## 📝 Notes

- Pastikan database sudah di-seed sebelum run test
- Test akan mengubah data di database (melakukan undian)
- Untuk test berulang, run `php artisan migrate:fresh --seed` lagi
- Test menggunakan login default: `farhan12` / `adminpass`

## 🎯 Expected Results

Setelah run semua test, Anda akan mendapatkan:
- ✅ Semua test passed
- 📊 Statistics untuk setiap event
- 📈 Analisis distribusi angka
- ✓ Verifikasi adaptive logic bekerja
- 📸 Screenshots dari setiap tahap
- 🎥 Video recording dari test run
