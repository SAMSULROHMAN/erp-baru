<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $adminRole = \App\Models\Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );

        $managerRole = \App\Models\Role::firstOrCreate(
            ['name' => 'manager'],
            ['description' => 'Manager']
        );

        $staffRole = \App\Models\Role::firstOrCreate(
            ['name' => 'staff'],
            ['description' => 'Staff']
        );

        // Create admin user
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@erp.test',
            'password' => bcrypt('password123'),
            'role_id' => $adminRole->id,
            'department' => 'IT',
            'status' => 'active'
        ]);

        // Create sample users
        User::factory(5)->create([
            'role_id' => $staffRole->id,
            'status' => 'active'
        ]);

        // Create categories
        $categories = \App\Models\Category::factory(5)->create();

        // Create products
        \App\Models\Product::factory(20)->create()->each(function ($product) use ($categories) {
            $product->category_id = $categories->random()->id;
            $product->stock_quantity = rand(10, 500);
            $product->save();
        });

        // Create suppliers
        \App\Models\Supplier::factory(10)->create();

        // Create customers
        \App\Models\Customer::factory(15)->create();

        // Create chart of accounts
        $assetAccount = \App\Models\ChartOfAccount::create([
            'code' => '1000',
            'name' => 'Kas (Cash)',
            'type' => 'asset',
            'sub_type' => 'current',
            'balance' => 10000000,
            'is_active' => true
        ]);

        $receivableAccount = \App\Models\ChartOfAccount::create([
            'code' => '1100',
            'name' => 'Piutang Usaha (Accounts Receivable)',
            'type' => 'asset',
            'sub_type' => 'current',
            'balance' => 0,
            'is_active' => true
        ]);

        $inventoryAccount = \App\Models\ChartOfAccount::create([
            'code' => '1200',
            'name' => 'Persediaan (Inventory)',
            'type' => 'asset',
            'sub_type' => 'current',
            'balance' => 5000000,
            'is_active' => true
        ]);

        $payableAccount = \App\Models\ChartOfAccount::create([
            'code' => '2000',
            'name' => 'Utang Usaha (Accounts Payable)',
            'type' => 'liability',
            'balance' => 0,
            'is_active' => true
        ]);

        $capitalAccount = \App\Models\ChartOfAccount::create([
            'code' => '3000',
            'name' => 'Modal Usaha (Capital)',
            'type' => 'equity',
            'balance' => 15000000,
            'is_active' => true
        ]);

        $revenueAccount = \App\Models\ChartOfAccount::create([
            'code' => '4000',
            'name' => 'Pendapatan Penjualan (Sales Revenue)',
            'type' => 'income',
            'balance' => 0,
            'is_active' => true
        ]);

        $expenseAccount = \App\Models\ChartOfAccount::create([
            'code' => '5000',
            'name' => 'Biaya Operasional (Operating Expense)',
            'type' => 'expense',
            'balance' => 0,
            'is_active' => true
        ]);

        // Create sample purchase orders
        \App\Models\PurchaseOrder::factory(5)->create()->each(function ($po) {
            $po->items()->createMany(
                \App\Models\PurchaseOrderItem::factory(rand(2, 4))->make()->toArray()
            );
        });

        // Create sample sales orders
        \App\Models\SalesOrder::factory(8)->create()->each(function ($so) {
            $so->items()->createMany(
                \App\Models\SalesOrderItem::factory(rand(2, 4))->make()->toArray()
            );
        });

        // Create BOM for some products
        $products = \App\Models\Product::all();
        if (count($products) > 2) {
            $finishedGood = $products->first();
            $materials = $products->skip(1)->take(3)->all();

            foreach ($materials as $material) {
                \App\Models\BomItem::create([
                    'product_id' => $finishedGood->id,
                    'material_product_id' => $material->id,
                    'quantity_required' => rand(1, 5),
                    'unit' => 'pcs',
                    'estimated_cost' => $material->cost_price * rand(1, 5)
                ]);
            }
        }

        // Create sample production orders
        $finishedGood = \App\Models\Product::first();
        if ($finishedGood && $finishedGood->bomItems()->exists()) {
            \App\Models\ProductionOrder::factory(3)->create([
                'product_id' => $finishedGood->id
            ]);
        }

        // Create sample journals
        $user = User::first();
        \App\Models\Journal::factory(5)->create([
            'created_by' => $user->id
        ])->each(function ($journal) use ($assetAccount, $payableAccount) {
            // Create balanced journal details
            \App\Models\JournalDetail::create([
                'journal_id' => $journal->id,
                'chart_of_account_id' => $assetAccount->id,
                'debit' => 1000000,
                'credit' => 0,
            ]);

            \App\Models\JournalDetail::create([
                'journal_id' => $journal->id,
                'chart_of_account_id' => $payableAccount->id,
                'debit' => 0,
                'credit' => 1000000,
            ]);
        });

        // Create sample invoices
        \App\Models\Invoice::factory(5)->create()->each(function ($invoice) {
            $invoice->items()->createMany(
                \App\Models\InvoiceItem::factory(rand(1, 3))->make()->toArray()
            );
        });

        echo "\n✅ Database seeding completed successfully!\n";
        echo "👤 Admin account: admin@erp.test / password123\n";
        echo "📊 Dengan 20 products, 10 suppliers, 15 customers, dan sample data lainnya\n";
    }
}
