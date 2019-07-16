<?php

use Illuminate\Database\Seeder;

class UserDetailsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        \App\User::create([
            'name' => "Gayan Sandaruwan",
            'email' => "gayan@ciperlabs.com",
            'password' => Hash::make('qwer1234'),
        ]);
    }
}
