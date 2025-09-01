<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Review;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Buat pengguna Admin default
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'address' => '123 Admin Street, Jakarta, Indonesia',
            'email_verified_at' => now(),
        ]);

        // 2. Buat pengguna Pelanggan default
        $defaultCustomer = User::create([
            'name' => 'Pelanggan',
            'email' => 'pelanggan@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '089876543210',
            'address' => '456 Customer Avenue, Bandung, Indonesia',
            'email_verified_at' => now(),
        ]);

        // 3. Buat 20 pengguna pelanggan acak menggunakan factory
        $randomCustomers = User::factory()->count(20)->create();

        // Gabungkan semua pelanggan untuk dibuatkan pesanan
        $allCustomers = $randomCustomers->push($defaultCustomer);

        // Ambil semua template yang ada
        $templates = Template::all();

        // Jika tidak ada template, tidak bisa membuat pesanan
        if ($templates->isEmpty()) {
            $this->command->warn('Tidak ada template ditemukan. Melewatkan pembuatan pesanan dummy.');
            return;
        }

        // 4. Buat pesanan dummy untuk setiap pelanggan
        $allCustomers->each(function (User $customer) use ($templates) {
            // Buat 1 sampai 5 pesanan acak untuk setiap pelanggan
            $orderCount = rand(1, 5);

            for ($i = 0; $i < $orderCount; $i++) {
                $orderItemsData = [];
                $subtotal = 0;
                $itemCount = rand(1, 3);

                // Buat item untuk pesanan
                for ($j = 0; $j < $itemCount; $j++) {
                    $template = $templates->random();
                    $quantity = rand(1, 2);
                    $price = 150000; // Harga statis
                    $subtotal += $quantity * $price;

                    $orderItemsData[] = [
                        'template_id' => $template->id,
                        'file_name' => 'https://res.cloudinary.com/demo/image/upload/sample.jpg', // URL desain dummy
                        'size' => collect(['S', 'M', 'L', 'XL'])->random(),
                        'quantity' => $quantity,
                        'price' => $price,
                    ];
                }

                // Buat data pembayaran utama
                $shippingCost = collect([18000, 25000, 35000])->random();
                $totalAmount = $subtotal + $shippingCost;
                $shippingStatus = collect(['processing', 'shipped', 'delivered'])->random();
                $paymentStatus = 'success'; // Asumsikan semua pesanan dummy lunas

                $payment = Payment::create([
                    'user_id' => $customer->id,
                    'order_id' => 'DUMMY-' . strtoupper(uniqid()),
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'shipping_service' => 'JNE Express - REG (Reguler)',
                    'shipping_cost' => $shippingCost,
                    'amount' => $totalAmount,
                    'status' => $paymentStatus,
                    'shipping_status' => $shippingStatus,
                    'shipped_at' => ($shippingStatus === 'shipped' || $shippingStatus === 'delivered') ? now()->subDays(rand(5, 10)) : null,
                    'delivered_at' => ($shippingStatus === 'delivered') ? now()->subDays(rand(1, 4)) : null,
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ]);

                // Simpan item pesanan
                $payment->orderItems()->createMany($orderItemsData);

                // Buat ulasan jika pesanan sudah diterima
                if ($payment->shipping_status === 'delivered') {
                    $itemToReview = $payment->orderItems->random();
                    if ($itemToReview->template_id) {
                        Review::create([
                            'user_id' => $customer->id,
                            'template_id' => $itemToReview->template_id,
                            'payment_id' => $payment->id,
                            'rating' => rand(3, 5),
                            'comment' => fake()->paragraph(rand(1, 3)),
                            'created_at' => $payment->delivered_at->addHours(rand(1, 24)),
                        ]);
                    }
                }
            }
        });
    }
}
