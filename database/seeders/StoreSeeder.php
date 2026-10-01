<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = $this->catalog();
        $productImages = $this->ensureDemoImagePool('products', 'demo_product', 12, 800, 1000);
        $categoryImages = $this->ensureDemoImagePool('categories', 'demo_category', 10, 720, 720);
        $productImageIndex = 0;
        $categoryImageIndex = 0;

        foreach ($catalog as $categoryName => $products) {
            $category = Category::query()->create([
                'name' => $categoryName,
                'image' => $categoryImages[$categoryImageIndex++ % count($categoryImages)],
            ]);

            foreach ($products as $product) {
                $timestamps = $this->catalogTimestamps($product['name']);

                Product::query()->create([
                    'category_id' => $category->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'stock' => $product['stock'],
                    'status' => $product['status'],
                    'image' => $productImages[$productImageIndex++ % count($productImages)],
                    'created_at' => $timestamps['created_at'],
                    'updated_at' => $timestamps['updated_at'],
                ]);
            }
        }
    }

    /**
     * @return array<string, list<array{name: string, description: string, price: float, stock: int, status: string}>>
     */
    private function catalog(): array
    {
        return [
            'Electronics & Audio' => [
                ['name' => 'NovaSound Wireless Earbuds', 'description' => 'Compact earbuds with charging case and 24-hour battery life.', 'price' => 49.99, 'stock' => 38, 'status' => 'active'],
                ['name' => 'PulseFit Smart Watch', 'description' => 'Heart-rate tracking, sleep insights, and interchangeable bands.', 'price' => 129.00, 'stock' => 22, 'status' => 'active'],
                ['name' => 'ClearView 27" Monitor', 'description' => '1080p IPS display with slim bezels for home office setups.', 'price' => 189.99, 'stock' => 14, 'status' => 'active'],
                ['name' => 'StreamLite USB Microphone', 'description' => 'Plug-and-play mic for meetings, podcasts, and gaming.', 'price' => 64.50, 'stock' => 3, 'status' => 'active'],
                ['name' => 'PowerHub 6-Port Charger', 'description' => 'GaN fast charger with two USB-C and four USB-A ports.', 'price' => 39.95, 'stock' => 41, 'status' => 'active'],
                ['name' => 'RetroWave Bluetooth Speaker', 'description' => 'Portable speaker with warm analog-style controls.', 'price' => 79.00, 'stock' => 0, 'status' => 'active'],
                ['name' => 'StudioPro Over-Ear Headphones', 'description' => 'Wired reference headphones for editing and mixing.', 'price' => 149.99, 'stock' => 8, 'status' => 'inactive'],
            ],
            'Home & Kitchen' => [
                ['name' => 'BrewCraft Pour-Over Kettle', 'description' => 'Gooseneck kettle with temperature presets for coffee lovers.', 'price' => 58.00, 'stock' => 19, 'status' => 'active'],
                ['name' => 'SilkLine Nonstick Skillet', 'description' => '10-inch skillet with stay-cool handle and even heating.', 'price' => 34.99, 'stock' => 27, 'status' => 'active'],
                ['name' => 'FreshSeal Glass Storage Set', 'description' => 'Stackable meal prep containers with snap-lock lids.', 'price' => 42.50, 'stock' => 33, 'status' => 'active'],
                ['name' => 'AromaStone Diffuser', 'description' => 'Ultrasonic diffuser with soft ambient lighting.', 'price' => 29.99, 'stock' => 2, 'status' => 'active'],
                ['name' => 'CloudRest Memory Foam Pillow', 'description' => 'Cooling gel layer for side and back sleepers.', 'price' => 54.00, 'stock' => 45, 'status' => 'active'],
                ['name' => 'ChefLine Knife Trio', 'description' => 'Chef, utility, and paring knives with protective sheaths.', 'price' => 89.99, 'stock' => 11, 'status' => 'active'],
            ],
            'Clothing & Shoes' => [
                ['name' => 'TrailFlex Hiking Boots', 'description' => 'Water-resistant boots with cushioned ankle support.', 'price' => 98.00, 'stock' => 16, 'status' => 'active'],
                ['name' => 'UrbanWeave Crew Sweater', 'description' => 'Mid-weight merino blend sweater for layering.', 'price' => 68.50, 'stock' => 24, 'status' => 'active'],
                ['name' => 'Daybreak Running Shoes', 'description' => 'Lightweight trainers with responsive foam midsole.', 'price' => 112.00, 'stock' => 4, 'status' => 'active'],
                ['name' => 'Coastline Linen Shirt', 'description' => 'Breathable relaxed-fit shirt for warm weather.', 'price' => 44.99, 'stock' => 31, 'status' => 'active'],
                ['name' => 'NorthField Puffer Jacket', 'description' => 'Packable insulated jacket with storm cuffs.', 'price' => 139.00, 'stock' => 0, 'status' => 'active'],
                ['name' => 'Classic Denim Jacket', 'description' => 'Medium-wash jacket with reinforced stitching.', 'price' => 79.99, 'stock' => 18, 'status' => 'inactive'],
            ],
            'Sports & Fitness' => [
                ['name' => 'CoreBalance Yoga Mat', 'description' => '6mm mat with alignment marks and carrying strap.', 'price' => 36.00, 'stock' => 52, 'status' => 'active'],
                ['name' => 'IronGrip Kettlebell 16kg', 'description' => 'Cast iron kettlebell with matte finish handle.', 'price' => 59.99, 'stock' => 9, 'status' => 'active'],
                ['name' => 'HydroTrack Steel Bottle', 'description' => '32oz insulated bottle keeps drinks cold for 24 hours.', 'price' => 27.50, 'stock' => 60, 'status' => 'active'],
                ['name' => 'FlexBand Resistance Set', 'description' => 'Five bands with door anchor and exercise guide.', 'price' => 24.99, 'stock' => 1, 'status' => 'active'],
                ['name' => 'SwiftStride Jump Rope', 'description' => 'Speed rope with adjustable length and ball bearings.', 'price' => 18.00, 'stock' => 40, 'status' => 'active'],
                ['name' => 'Summit Trekking Poles', 'description' => 'Collapsible aluminum poles with cork grips.', 'price' => 69.00, 'stock' => 13, 'status' => 'active'],
            ],
            'Beauty & Personal Care' => [
                ['name' => 'GlowKind Vitamin C Serum', 'description' => 'Daily brightening serum for dull or tired skin.', 'price' => 22.99, 'stock' => 28, 'status' => 'active'],
                ['name' => 'PureMist Facial Cleanser', 'description' => 'Gentle gel cleanser suitable for sensitive skin.', 'price' => 16.50, 'stock' => 35, 'status' => 'active'],
                ['name' => 'SilkTouch Hair Dryer', 'description' => 'Ionic dryer with diffuser and cool-shot button.', 'price' => 74.00, 'stock' => 7, 'status' => 'active'],
                ['name' => 'CalmWave Essential Oil Set', 'description' => 'Lavender, eucalyptus, and citrus blends for diffusers.', 'price' => 19.99, 'stock' => 44, 'status' => 'active'],
                ['name' => 'NightRepair Moisturizer', 'description' => 'Rich cream with ceramides for overnight hydration.', 'price' => 31.00, 'stock' => 0, 'status' => 'active'],
                ['name' => 'FreshMint Electric Toothbrush', 'description' => 'Rechargeable brush with two-minute timer.', 'price' => 45.99, 'stock' => 21, 'status' => 'active'],
            ],
            'Office & Stationery' => [
                ['name' => 'FocusDesk Ergonomic Chair', 'description' => 'Adjustable lumbar support and breathable mesh back.', 'price' => 249.00, 'stock' => 6, 'status' => 'active'],
                ['name' => 'NoteWell Dot Grid Notebook', 'description' => 'Hardcover A5 notebook with 160 pages.', 'price' => 14.99, 'stock' => 80, 'status' => 'active'],
                ['name' => 'Precision Gel Pen Pack', 'description' => 'Smooth-writing pens in black, blue, and red.', 'price' => 9.50, 'stock' => 120, 'status' => 'active'],
                ['name' => 'CableNest Desk Organizer', 'description' => 'Desktop tray for pens, clips, and charging cables.', 'price' => 18.99, 'stock' => 3, 'status' => 'active'],
                ['name' => 'BrightLite Desk Lamp', 'description' => 'LED lamp with warm and cool color modes.', 'price' => 42.00, 'stock' => 17, 'status' => 'active'],
                ['name' => 'Archive Box Set', 'description' => 'Three labeled storage boxes for documents.', 'price' => 26.50, 'stock' => 25, 'status' => 'inactive'],
            ],
            'Toys & Games' => [
                ['name' => 'StarQuest Board Game', 'description' => 'Strategy game for 2–4 players, 45-minute play time.', 'price' => 38.99, 'stock' => 15, 'status' => 'active'],
                ['name' => 'BuildCity Block Set', 'description' => '120-piece colorful building blocks for ages 4+.', 'price' => 29.00, 'stock' => 32, 'status' => 'active'],
                ['name' => 'Puzzle Harbor 1000 Pieces', 'description' => 'Coastal village scene jigsaw with poster guide.', 'price' => 21.99, 'stock' => 2, 'status' => 'active'],
                ['name' => 'RocketRacers Remote Cars', 'description' => 'Twin-pack RC cars with rechargeable batteries.', 'price' => 54.50, 'stock' => 10, 'status' => 'active'],
                ['name' => 'StoryTime Plush Bear', 'description' => 'Soft hypoallergenic teddy with embroidered details.', 'price' => 24.00, 'stock' => 48, 'status' => 'active'],
                ['name' => 'LogicLab STEM Kit', 'description' => 'Intro electronics kit with guided experiments.', 'price' => 49.99, 'stock' => 0, 'status' => 'active'],
            ],
            'Garden & Outdoor' => [
                ['name' => 'GreenThumb Pruning Shears', 'description' => 'Sharp bypass shears with comfort grip handles.', 'price' => 23.99, 'stock' => 26, 'status' => 'active'],
                ['name' => 'PatioGlow String Lights', 'description' => 'Weather-resistant 24ft lights for outdoor spaces.', 'price' => 32.00, 'stock' => 37, 'status' => 'active'],
                ['name' => 'HarvestBox Herb Planter', 'description' => 'Windowsill planter with basil, mint, and thyme seeds.', 'price' => 19.50, 'stock' => 5, 'status' => 'active'],
                ['name' => 'TrailCamp Folding Chair', 'description' => 'Lightweight chair with cup holder and carry bag.', 'price' => 41.99, 'stock' => 14, 'status' => 'active'],
                ['name' => 'RainShield Hose Nozzle', 'description' => 'Eight-pattern nozzle with ergonomic trigger.', 'price' => 15.99, 'stock' => 55, 'status' => 'active'],
                ['name' => 'WildBird Feeder Station', 'description' => 'Dual feeder pole with squirrel baffle.', 'price' => 58.00, 'stock' => 8, 'status' => 'active'],
            ],
            'Pet Supplies' => [
                ['name' => 'HappyPaws Dry Food 12lb', 'description' => 'Chicken and brown rice recipe for adult dogs.', 'price' => 42.99, 'stock' => 30, 'status' => 'active'],
                ['name' => 'CozyNest Cat Bed', 'description' => 'Donut bed with washable removable cover.', 'price' => 34.50, 'stock' => 1, 'status' => 'active'],
                ['name' => 'FetchMaster Rope Toy', 'description' => 'Durable cotton rope toy for medium breeds.', 'price' => 11.99, 'stock' => 64, 'status' => 'active'],
                ['name' => 'AquaFlow Pet Fountain', 'description' => 'Quiet circulating water fountain with filter.', 'price' => 39.00, 'stock' => 12, 'status' => 'active'],
                ['name' => 'TravelTrek Carrier Bag', 'description' => 'Ventilated soft carrier for cats and small dogs.', 'price' => 47.99, 'stock' => 9, 'status' => 'active'],
                ['name' => 'GroomPro Deshedding Brush', 'description' => 'Stainless steel brush for long and short coats.', 'price' => 18.50, 'stock' => 23, 'status' => 'active'],
            ],
            'Snacks & Beverages' => [
                ['name' => 'Mountain Roast Coffee Beans', 'description' => 'Single-origin medium roast, 12oz whole bean.', 'price' => 16.99, 'stock' => 50, 'status' => 'active'],
                ['name' => 'Golden Valley Honey Jar', 'description' => 'Raw wildflower honey sourced from local apiaries.', 'price' => 12.50, 'stock' => 28, 'status' => 'active'],
                ['name' => 'SeaSalt Olive Oil Crackers', 'description' => 'Crispy crackers baked with extra virgin olive oil.', 'price' => 4.99, 'stock' => 90, 'status' => 'active'],
                ['name' => 'CitrusSpark Cold Brew Tea', 'description' => 'Caffeine-light tea bags for iced brewing.', 'price' => 8.99, 'stock' => 0, 'status' => 'active'],
                ['name' => 'DarkCacao Chocolate Bar', 'description' => '72% dark chocolate with sea salt flakes.', 'price' => 5.50, 'stock' => 2, 'status' => 'active'],
                ['name' => 'TrailMix Adventure Pack', 'description' => 'Almonds, cranberries, and dark chocolate chips.', 'price' => 7.25, 'stock' => 75, 'status' => 'active'],
            ],
        ];
    }

    /**
     * @return list<string> Storage paths relative to the public disk
     */
    private function ensureDemoImagePool(string $directory, string $basename, int $count, int $width, int $height): array
    {
        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $filename = $basename.'_'.str_pad((string) $i, 2, '0', STR_PAD_LEFT).'.jpg';
            $path = $directory.'/'.$filename;

            if (! Storage::disk('public')->exists($path)) {
                $generated = UploadedFile::fake()->image($filename, $width, $height);
                Storage::disk('public')->put($path, (string) file_get_contents($generated->getPathname()));
            }

            $paths[] = $path;
        }

        return $paths;
    }

    /**
     * @return array{created_at: \Illuminate\Support\Carbon, updated_at: \Illuminate\Support\Carbon}
     */
    private function catalogTimestamps(string $productName): array
    {
        $recentNames = [
            'NovaSound Wireless Earbuds',
            'PulseFit Smart Watch',
            'BrewCraft Pour-Over Kettle',
            'CoreBalance Yoga Mat',
            'GlowKind Vitamin C Serum',
            'StarQuest Board Game',
            'Mountain Roast Coffee Beans',
            'BrightLite Desk Lamp',
            'HydroTrack Steel Bottle',
            'NoteWell Dot Grid Notebook',
        ];

        if (in_array($productName, $recentNames, true)) {
            $at = now()->subDays(random_int(2, 12));

            return ['created_at' => $at, 'updated_at' => $at];
        }

        $at = now()->subMonths(random_int(2, 9))->subDays(random_int(0, 24));

        return ['created_at' => $at, 'updated_at' => $at];
    }
}
