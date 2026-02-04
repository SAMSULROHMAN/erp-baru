# QUICK START GUIDE - Sistem ERP Laravel

## 🚀 Instalasi Cepat

```bash
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Konfigurasi database di .env
# DB_DATABASE=erp
# DB_USERNAME=root
# DB_PASSWORD=

# 4. Run migrations
php artisan migrate

# 5. Seed database dengan sample data
php artisan db:seed

# 6. Start development server
php artisan serve
# Server akan berjalan di http://localhost:8000
```

## 📊 Struktur Project

```
erp/
├── app/
│   ├── Models/               # Eloquent Models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── PurchaseOrder.php
│   │   ├── SalesOrder.php
│   │   ├── ProductionOrder.php
│   │   ├── Journal.php
│   │   ├── Invoice.php
│   │   └── ...
│   │
│   ├── Http/Controllers/Api/ # API Controllers
│   │   ├── UserController.php
│   │   ├── ProductController.php
│   │   ├── SupplierController.php
│   │   ├── PurchaseOrderController.php
│   │   ├── SalesOrderController.php
│   │   ├── ProductionOrderController.php
│   │   ├── JournalController.php
│   │   ├── InvoiceController.php
│   │   └── PaymentController.php
│   │
│   ├── Services/             # Business Logic Services
│   └── Traits/              # Traits (ApiResponse, dll)
│
├── database/
│   ├── migrations/           # Database Migrations
│   ├── factories/            # Model Factories untuk testing
│   └── seeders/              # Database Seeders
│
├── routes/
│   └── api.php               # API Routes (Semua endpoint ada di sini)
│
├── tests/
│   └── Feature/              # Feature Tests
│       ├── UserControllerTest.php
│       ├── ProductControllerTest.php
│       ├── PurchaseOrderControllerTest.php
│       ├── SalesOrderControllerTest.php
│       ├── ProductionOrderControllerTest.php
│       ├── JournalControllerTest.php
│       └── InvoiceControllerTest.php
│
└── DOKUMENTASI_ERP.md        # Dokumentasi lengkap
```

## 🔐 Default Login Account

```
Email: admin@erp.test
Password: password123
```

## 📝 Fitur Utama

### 1️⃣ User Management (`/api/users`)
- Tambah user dengan role
- Edit user
- List dan filter users

### 2️⃣ Inventory Management (`/api/products`, `/api/suppliers`)
- Manage produk dan kategori
- Track stock dengan reorder level
- Manage supplier

### 3️⃣ Purchase Orders (`/api/purchase-orders`)
- Create PO dari supplier
- Track penerimaan barang
- Automatic stock update

### 4️⃣ Sales Orders (`/api/sales-orders`)
- Create SO untuk customer
- Confirm dan ship
- Auto-generate invoice
- Track payments

### 5️⃣ Production Management (`/api/production-orders`)
- Create production order
- Manage BOM (Bill of Materials)
- Track production progress
- Auto stock management

### 6️⃣ Finance & Accounting (`/api/journals`, `/api/chart-of-accounts`)
- Journal entry dengan double-entry bookkeeping
- Chart of accounts
- Balance validation
- Payment tracking

### 7️⃣ Invoicing (`/api/invoices`)
- Create & send invoice
- Record payment
- Track invoice status

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test tests/Feature/ProductControllerTest.php

# Run tests dengan verbose output
php artisan test --verbose

# Test hanya test method tertentu
php artisan test --filter=test_can_create_user
```

## 📡 API Endpoint Examples

### Create Purchase Order
```bash
curl -X POST http://localhost:8000/api/purchase-orders \
  -H "Content-Type: application/json" \
  -d '{
    "po_number": "PO-2024-001",
    "supplier_id": 1,
    "order_date": "2024-01-01",
    "expected_delivery_date": "2024-01-15",
    "tax": 100000,
    "items": [
      {
        "product_id": 1,
        "quantity": 10,
        "unit_price": 50000
      }
    ]
  }'
```

### Create Sales Order
```bash
curl -X POST http://localhost:8000/api/sales-orders \
  -H "Content-Type: application/json" \
  -d '{
    "so_number": "SO-2024-001",
    "customer_id": 1,
    "order_date": "2024-01-01",
    "items": [
      {
        "product_id": 1,
        "quantity": 5,
        "unit_price": 100000
      }
    ]
  }'
```

### Confirm Sales Order
```bash
curl -X PATCH http://localhost:8000/api/sales-orders/1/confirm
```

### Create Journal Entry
```bash
curl -X POST http://localhost:8000/api/journals \
  -H "Content-Type: application/json" \
  -d '{
    "journal_number": "J-2024-001",
    "type": "general",
    "journal_date": "2024-01-01",
    "details": [
      {
        "chart_of_account_id": 1,
        "debit": 1000000,
        "credit": 0
      },
      {
        "chart_of_account_id": 2,
        "debit": 0,
        "credit": 1000000
      }
    ]
  }'
```

## 🔍 Database Schema

### User Management
- `users` - User akun
- `roles` - Role/jabatan

### Inventory
- `products` - Master produk
- `categories` - Kategori produk
- `suppliers` - Master supplier
- `stock_movements` - History stok

### Purchasing
- `purchase_orders` - Header PO
- `purchase_order_items` - Detail PO items

### Sales
- `customers` - Master customer
- `sales_orders` - Header SO
- `sales_order_items` - Detail SO items
- `invoices` - Invoice header
- `invoice_items` - Invoice items
- `payments` - Payment tracking

### Production
- `production_orders` - Production order
- `bom_items` - Bill of Materials

### Finance
- `chart_of_accounts` - Chart of accounts
- `journals` - Journal entries
- `journal_details` - Journal detail items

## 🛠️ Customization

### Tambah Field Baru ke Produk

1. **Create Migration:**
   ```bash
   php artisan make:migration add_sku_to_products
   ```

2. **Edit migration:**
   ```php
   Schema::table('products', function (Blueprint $table) {
       $table->string('sku')->unique()->nullable();
   });
   ```

3. **Run migration:**
   ```bash
   php artisan migrate
   ```

4. **Update Product Model:**
   ```php
   protected $fillable = [
       ...,
       'sku'
   ];
   ```

5. **Update Controller validation**

### Tambah New Module

1. Create Model: `php artisan make:model NewModel -m`
2. Create Controller: `php artisan make:controller Api/NewModelController`
3. Add routes di `routes/api.php`
4. Create tests di `tests/Feature/`

## 📚 Documentation Reference

- **Full Documentation**: Lihat `DOKUMENTASI_ERP.md`
- **API Endpoints**: Semua endpoint lengkap di dokumentasi
- **Workflow Guide**: Purchase → Sales → Production → Finance

## ⚙️ Configuration

### Database
Edit `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp
DB_USERNAME=root
DB_PASSWORD=
```

### App Settings
```
APP_NAME=ERP
APP_ENV=local
APP_DEBUG=true
```

## 🐛 Troubleshooting

### Error: "No database selected"
- Pastikan database sudah dibuat di MySQL
- Check `.env` DB_DATABASE benar

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "Migration not run"
```bash
php artisan migrate --fresh
php artisan db:seed
```

### Clear cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## 📞 Support

Untuk dokumentasi lengkap, buka `DOKUMENTASI_ERP.md`

## ✅ Checklist Sebelum Go Live

- [ ] Database migrations semuanya run
- [ ] Sample data sudah di-seed
- [ ] Semua tests pass
- [ ] Environment .env sudah di-setup
- [ ] User roles sudah di-setup
- [ ] Chart of Accounts sudah di-setup

---

**Happy coding! 🚀**
