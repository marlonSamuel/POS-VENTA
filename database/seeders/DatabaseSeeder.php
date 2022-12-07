<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run()
    {
        User::create(array('username' => 'marlon','name'=>'marlon','lastname'=>'marlon','password'=>bcrypt('marlon'),'role'=>'admin'));
        User::create(array('username' => 'lalas','name'=>'lalas','lastname'=>'aguilar','password'=>bcrypt('lalas'),'role'=>'admin'));
        $this->command->info('User table seeded!');
    }
}
