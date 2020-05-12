<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        factory(\App\User::class)->create([
            'email' => 'trial1@gmail.com',
            'name' => 'trial1'
        ]);

        factory(\App\User::class)->create([
            'email' => 'trial2@gmail.com',
            'name' => 'trial2'
        ]);

        factory(\App\User::class)->create([
            'email' => 'trial3@gmail.com',
            'name' => 'trial3'
        ]);
    }
}
