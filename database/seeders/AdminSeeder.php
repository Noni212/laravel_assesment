<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $detail = [
            'name' => "Admin",
            'user_name'  => "Admin",
            'email' => "admin@admin.com",
            'user_role' => 'admin',
            'password' => bcrypt("admin12345"),
        ];

        $user = User::create($detail);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;
    }
}
