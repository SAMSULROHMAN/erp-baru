# Sistem ERP Sederhana - Laravel

Sistem Enterprise Resource Planning (ERP) yang komprehensif dan sederhana dibangun menggunakan Framework Laravel. Sistem ini mengintegrasikan modul manajemen user, inventory, finance, production, dan sales.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Struktur Database](#struktur-database)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Panduan Penggunaan](#panduan-penggunaan)

## Fitur Utama

### 1. **Manajemen User & Role**
- Membuat, membaca, mengubah, dan menghapus user
- Manajemen role/jabatan
- Status user (active/inactive)
- Tracking pengguna untuk setiap aktivitas

### 2. **Manajemen Inventory**
- Manajemen produk dengan kategori
- Tracking stok produk
- Alert ketika stok rendah (reorder level)
- Tracking supplier
- Stock movement history

### 3. **Manajemen Pembelian (Purchase Order)**
- Membuat purchase order dari supplier
- Status tracking: draft → submitted → received → closed
- Tracking penerimaan barang
- Automatic stock update saat barang diterima
- Perhitungan pajak dan total otomatis

### 4. **Manajemen Penjualan (Sales Order)**
- Membuat sales order untuk customer
- Tracking status: draft → confirmed → shipped → delivered
- Validasi stok sebelum confirm
- Automatic stock deduction saat shipping
- Integration dengan invoice generation

### 5. **Manajemen Produksi**
- Membuat production order
- Bill of Materials (BOM) management
- Production scheduling
- Real-time production progress tracking
- Automatic stock deduction untuk materials
- Automatic stok addition untuk finished goods

### 6. **Manajemen Finance & Akuntansi**
- Chart of Accounts (CoA)
- Journal entry dengan double-entry bookkeeping
- Balance validation untuk setiap journal
- Posting journal dengan tracking siapa yang post
- Account balance tracking
- Payment tracking (incoming & outgoing)

### 7. **Manajemen Customer & Invoice**
- Customer management dengan credit limit
- Invoice generation dari sales order
- Invoice payment tracking
- Automatic status update saat paid
- Multiple payment methods support

## Struktur Database

### Database Diagram Overview

```
users → roles (one-to-many)
products → categories (many-to-one)
products → stock_movements (one-to-many)
products → bom_items (one-to-many)

suppliers → purchase_orders (one-to-many)
purchase_orders → purchase_order_items (one-to-many)
purchase_order_items → products (many-to-one)

customers → sales_orders (one-to-many)
customers → invoices (one-to-many)
sales_orders → sales_order_items (one-to-many)
sales_order_items → products (many-to-one)
sales_orders → invoices (one-to-one)

production_orders → products (many-to-one)
bom_items → production_orders (indirect via products)

journals → journal_details (one-to-many)
journal_details → chart_of_accounts (many-to-one)

invoices → invoice_items (one-to-many)
payments → customers/suppliers (many-to-one)
```

### Tabel Utama

1. **users** - User akun sistem
2. **roles** - Role/jabatan pengguna
3. **products** - Master data produk
4. **categories** - Kategori produk
5. **suppliers** - Master data supplier
6. **customers** - Master data customer
7. **stock_movements** - History pergerakan stok
8. **purchase_orders** - Purchase order header
9. **purchase_order_items** - Detail items PO
10. **sales_orders** - Sales order header
11. **sales_order_items** - Detail items SO
12. **production_orders** - Production order
13. **bom_items** - Bill of Materials
14. **chart_of_accounts** - Chart of accounts akuntansi
15. **journals** - Journal entry header
16. **journal_details** - Detail journal entry
17. **invoices** - Invoice header
18. **invoice_items** - Detail invoice items
19. **payments** - Payment tracking

## Instalasi

### 1. Clone Repository
```bash
git clone <repository-url>
cd erp
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Setup Environment
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Configuration
Edit file `.env`:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=erp
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Run Seeders (Opsional)
```bash
php artisan db:seed
```

### 7. Start Development Server
```bash
php artisan serve
npm run dev
```

## Konfigurasi

### Database
- Edit `.env` dengan database credentials Anda
- Migrasi akan membuat semua table secara otomatis

### API
- Base URL: `http://localhost:8000/api`
- Semua response dalam format JSON

## API Endpoints

### Authentication
```
GET  /api/users              - List users
POST /api/users              - Create user
GET  /api/users/{id}         - Show user
PUT  /api/users/{id}         - Update user
DELETE /api/users/{id}       - Delete user
GET  /api/users/roles        - Get all roles
POST /api/users/create-role  - Create role
```

### Products & Categories
```
GET    /api/products                      - List products
POST   /api/products                      - Create product
GET    /api/products/{id}                 - Show product
PUT    /api/products/{id}                 - Update product
DELETE /api/products/{id}                 - Delete product
GET    /api/products/low-stock            - Low stock products
GET    /api/products/categories           - List categories
POST   /api/products/create-category      - Create category
```

### Suppliers
```
GET    /api/suppliers        - List suppliers
POST   /api/suppliers        - Create supplier
GET    /api/suppliers/{id}   - Show supplier
PUT    /api/suppliers/{id}   - Update supplier
DELETE /api/suppliers/{id}   - Delete supplier
```

### Purchase Orders
```
GET    /api/purchase-orders/{id}              - Show PO
POST   /api/purchase-orders                   - Create PO
PUT    /api/purchase-orders/{id}              - Update PO
PATCH  /api/purchase-orders/{id}/submit       - Submit PO
PATCH  /api/purchase-orders/{id}/receive      - Receive PO
PATCH  /api/purchase-orders/{id}/cancel       - Cancel PO
DELETE /api/purchase-orders/{id}              - Delete PO
```

### Customers
```
GET    /api/customers                          - List customers
POST   /api/customers                          - Create customer
GET    /api/customers/{id}                     - Show customer
GET    /api/customers/{id}/credit-info         - Credit info
PUT    /api/customers/{id}                     - Update customer
DELETE /api/customers/{id}                     - Delete customer
```

### Sales Orders
```
GET    /api/sales-orders                           - List SO
POST   /api/sales-orders                           - Create SO
GET    /api/sales-orders/{id}                      - Show SO
PUT    /api/sales-orders/{id}                      - Update SO
PATCH  /api/sales-orders/{id}/confirm              - Confirm SO
PATCH  /api/sales-orders/{id}/ship                 - Ship SO
PATCH  /api/sales-orders/{id}/cancel               - Cancel SO
POST   /api/sales-orders/{id}/create-invoice       - Create Invoice
DELETE /api/sales-orders/{id}                      - Delete SO
```

### Production Orders
```
GET    /api/production-orders                          - List PO
POST   /api/production-orders                          - Create PO
GET    /api/production-orders/{id}                     - Show PO
PUT    /api/production-orders/{id}                     - Update PO
PATCH  /api/production-orders/{id}/schedule            - Schedule PO
PATCH  /api/production-orders/{id}/start               - Start production
PATCH  /api/production-orders/{id}/report-production   - Report production
PATCH  /api/production-orders/{id}/complete            - Complete PO
PATCH  /api/production-orders/{id}/cancel              - Cancel PO
DELETE /api/production-orders/{id}                     - Delete PO
```

### BOM (Bill of Materials)
```
GET    /api/bom/product/{productId}    - Get BOM for product
POST   /api/bom/items                  - Add BOM item
PUT    /api/bom/items/{id}             - Update BOM item
DELETE /api/bom/items/{id}             - Delete BOM item
```

### Chart of Accounts
```
GET    /api/chart-of-accounts                   - List accounts
POST   /api/chart-of-accounts                   - Create account
GET    /api/chart-of-accounts/{id}              - Show account
GET    /api/chart-of-accounts/{id}/balance      - Get account balance
PUT    /api/chart-of-accounts/{id}              - Update account
DELETE /api/chart-of-accounts/{id}              - Delete account
```

### Journals
```
GET    /api/journals                   - List journals
POST   /api/journals                   - Create journal
GET    /api/journals/{id}              - Show journal
PUT    /api/journals/{id}              - Update journal
PATCH  /api/journals/{id}/post         - Post journal
DELETE /api/journals/{id}              - Delete journal
```

### Invoices
```
GET    /api/invoices                                - List invoices
POST   /api/invoices                                - Create invoice
GET    /api/invoices/{id}                           - Show invoice
PUT    /api/invoices/{id}                           - Update invoice
PATCH  /api/invoices/{id}/send                      - Send invoice
PATCH  /api/invoices/{id}/record-payment            - Record payment
DELETE /api/invoices/{id}                           - Delete invoice
```

### Payments
```
GET    /api/payments                    - List payments
POST   /api/payments                    - Create payment
GET    /api/payments/{id}               - Show payment
PUT    /api/payments/{id}               - Update payment
PATCH  /api/payments/{id}/confirm       - Confirm payment
PATCH  /api/payments/{id}/cancel        - Cancel payment
DELETE /api/payments/{id}               - Delete payment
```

## Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test
```bash
php artisan test tests/Feature/UserControllerTest.php
php artisan test tests/Feature/ProductControllerTest.php
php artisan test tests/Feature/PurchaseOrderControllerTest.php
php artisan test tests/Feature/SalesOrderControllerTest.php
php artisan test tests/Feature/ProductionOrderControllerTest.php
php artisan test tests/Feature/JournalControllerTest.php
php artisan test tests/Feature/InvoiceControllerTest.php
```

### Test Coverage
- **User Controller Tests** - User & role management
- **Product Controller Tests** - Product & category management
- **Supplier Controller Tests** - Supplier management
- **Purchase Order Tests** - PO creation, submission, receipt
- **Sales Order Tests** - SO creation, confirmation, shipping
- **Production Order Tests** - Production planning & execution
- **Journal Tests** - Journal creation, posting, balance validation
- **Invoice Tests** - Invoice creation, payment recording

## Panduan Penggunaan

### Workflow: Purchase Order

1. **Create Purchase Order**
   ```
   POST /api/purchase-orders
   {
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
   }
   ```

2. **Submit Purchase Order**
   ```
   PATCH /api/purchase-orders/1/submit
   ```

3. **Receive Purchase Order**
   ```
   PATCH /api/purchase-orders/1/receive
   {
     "items": [
       {
         "purchase_order_item_id": 1,
         "quantity": 10
       }
     ],
     "delivery_date": "2024-01-15"
   }
   ```
   ✅ Stock otomatis bertambah sebanyak 10

### Workflow: Sales Order

1. **Create Sales Order**
   ```
   POST /api/sales-orders
   {
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
   }
   ```

2. **Confirm Sales Order** (Validate stock)
   ```
   PATCH /api/sales-orders/1/confirm
   ```

3. **Ship Sales Order**
   ```
   PATCH /api/sales-orders/1/ship
   {
     "items": [
       {
         "sales_order_item_id": 1,
         "quantity": 5
       }
     ]
   }
   ```
   ✅ Stock otomatis berkurang sebanyak 5

4. **Create Invoice**
   ```
   POST /api/sales-orders/1/create-invoice
   ```

5. **Record Payment**
   ```
   PATCH /api/invoices/1/record-payment
   {
     "amount": 500000,
     "payment_date": "2024-01-05",
     "payment_method": "bank_transfer"
   }
   ```

### Workflow: Production Order

1. **Create Production Order** (harus ada BOM)
   ```
   POST /api/production-orders
   {
     "pro_number": "PRO-2024-001",
     "product_id": 1,
     "quantity": 50,
     "start_date": "2024-01-01"
   }
   ```

2. **Schedule Production**
   ```
   PATCH /api/production-orders/1/schedule
   ```

3. **Start Production**
   ```
   PATCH /api/production-orders/1/start
   ```
   ✅ Validasi material availability

4. **Report Production Result**
   ```
   PATCH /api/production-orders/1/report-production
   {
     "quantity_produced": 25
   }
   ```
   ✅ Material otomatis berkurang sesuai BOM
   ✅ Finished good otomatis bertambah

5. **Complete Production**
   ```
   PATCH /api/production-orders/1/complete
   ```

### Workflow: Journal Entry (Akuntansi)

1. **Create Journal Entry** (harus balanced: debit = credit)
   ```
   POST /api/journals
   {
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
   }
   ```

2. **Post Journal** (hanya draft yang bisa dipost)
   ```
   PATCH /api/journals/1/post
   ```
   ✅ Chart of accounts balance otomatis update

## Key Features & Best Practices

### 1. **Transaction Management**
- Semua operasi yang kompleks menggunakan database transaction
- Rollback otomatis jika ada error

### 2. **Validation**
- Server-side validation untuk semua input
- Business logic validation (e.g., stock availability)

### 3. **Authorization & Security**
- User authentication tracking (created_by)
- Soft delete support untuk data integrity

### 4. **Real-time Updates**
- Stock movements automatically tracked
- Account balances automatically updated
- Status changes properly validated

### 5. **Error Handling**
- Comprehensive error messages
- Proper HTTP status codes
- Transaction rollback on errors

### 6. **Testing**
- Unit tests untuk core functionality
- Integration tests untuk workflows
- Database seeding untuk test data

## Models & Relationships

Semua model telah didefinisikan dengan relationship yang tepat:

- ✅ User hasMany Role
- ✅ Product belongsTo Category
- ✅ PurchaseOrder hasMany PurchaseOrderItems
- ✅ SalesOrder hasMany SalesOrderItems
- ✅ ProductionOrder belongsTo Product
- ✅ BomItem connects Products (self-referencing)
- ✅ Journal hasMany JournalDetails
- ✅ Invoice hasMany InvoiceItems
- ✅ Customer hasMany SalesOrders & Invoices
- ✅ Payment tracking untuk Customer & Supplier

## Support untuk Development

### Customize Controllers
Semua controller telah distruktur dengan baik dan mudah untuk dikustomisasi.

### Add New Features
1. Buat migration untuk table baru
2. Buat model dengan relationship
3. Buat controller dengan resource methods
4. Add routes di `routes/api.php`
5. Buat tests untuk verifikasi

### Debugging
```bash
# Enable query logging
DB::enableQueryLog();

# Check queries
dd(DB::getQueryLog());

# Test specific feature
php artisan test --filter=testName
```

## License

MIT

## Author

Dikembangkan menggunakan Laravel Framework

---

**Catatan**: Sistem ini dirancang untuk pembelajaran dan usage sederhana. Untuk production use, tambahkan:
- Authentication middleware
- Rate limiting
- Input sanitization
- Detailed logging
- Performance optimization
- API documentation (Swagger/OpenAPI)
