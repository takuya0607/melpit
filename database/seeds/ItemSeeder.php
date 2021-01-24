<?php

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        factory(Item::class)->create([
            'id' => "1",
            'seller_id' => '1',
            'secondary_category_id' => '5',
            'item_condition_id' => '1',
            'name' => ' ジャケット',
            'image_file_name' => 'sample_images_1.jpeg',
            'description' => 'こちらは裏起毛の仕様で、とても暖かいジャケットになります。',
            'price' => '30000',
            'state' => 'selling',
        ]);
        factory(Item::class)->create([
            'id' => "2",
            'seller_id' => '1',
            'secondary_category_id' => '6',
            'item_condition_id' => '1',
            'name' => 'Timberland',
            'image_file_name' => 'sample_images_2.jpeg',
            'description' => 'こちらはTimberlandのブーツになります',
            'price' => '15000',
            'state' => 'selling',
        ]);
        factory(Item::class)->create([
            'id' => "3",
            'seller_id' => '1',
            'secondary_category_id' => '11',
            'item_condition_id' => '1',
            'name' => 'ハリーポッター全巻',
            'image_file_name' => 'sample_images_3.jpeg',
            'description' => 'こちらはハリーポッター全巻になります',
            'price' => '2000',
            'state' => 'selling',
        ]);
    }
}
