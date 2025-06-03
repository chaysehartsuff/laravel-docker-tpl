<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class InterpretScraperPreferenceData implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // find all sp links that have 'last_scraped_all' as null and content as not null
        // pull sp from sp link and get table definition for AI
        // have AI interpret each sp link content and return as many results from page in table format as JSON
        // loop through results and save each row to table from sp.table_name
    }
}
