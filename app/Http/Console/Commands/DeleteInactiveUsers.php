<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DeleteInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-inactive-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deletes users that are inactive';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Delete users with email_verified_at = null and username = null
        User::whereNull('email_verified_at')
            ->orWhereNull('username')
            ->delete();
    }
}
