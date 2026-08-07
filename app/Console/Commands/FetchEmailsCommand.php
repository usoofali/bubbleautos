<?php

namespace App\Console\Commands;

use App\Services\EmailFetchService;
use Illuminate\Console\Command;

class FetchEmailsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emails:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and process incoming shipping emails from operations@ankshipping.com / IMAP server';

    /**
     * Execute the console command.
     */
    public function handle(EmailFetchService $emailFetchService): int
    {
        $this->info('Starting incoming email fetch from shipping account...');

        $result = $emailFetchService->fetchLatestEmails();

        $this->info("Processed {$result['count']} email(s) for {$result['target_email']}.");

        return Command::SUCCESS;
    }
}
