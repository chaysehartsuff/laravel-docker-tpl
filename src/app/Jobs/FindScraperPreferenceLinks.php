<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\ScraperPreference;
use App\Models\ScraperPreferenceWeb;
use App\Models\Status;
use App\Util\AI;
use App\Util\GoogleSearch;
use Log;

class FindScraperPreferenceLinks implements ShouldQueue
{
    use Queueable;

    protected ScraperPreference $sp;
    protected int $min_url_results;

    /**
     * Create a new job instance.
     */
    public function __construct(ScraperPreference $sp, int $min_url_results)
    {
        $this->sp = $sp;
        $this->min_url_results = $min_url_results;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Status::set('sp.links.running.add.twcss', 'bg-green-400', $this->sp->id, $this->sp->user_id);
        Status::set('sp.links.running.rm.twcss', 'bg-red-400', $this->sp->id, $this->sp->user_id);
        try {
            $new_links_added = 0;
            $page = 1;
            while($new_links_added < $this->min_url_results) {
                $urls = GoogleSearch::findScraperPreferenceLinks($this->sp, $this->min_url_results, $page);
                if(empty($urls)) {
                    break;
                }
                foreach ($urls as $url) {
                    if(!ScraperPreferenceWeb::exists($this->sp, $url)) {
                        $spw = new ScraperPreferenceWeb();
                        $spw->scraper_preference_id = $this->sp->id;
                        $spw->url = $url;
                        $spw->save();
                        $new_links_added++;
                    }
                }
                $page++;
            }
        } catch (\Exception $e) {
                Status::set('sp.links.running.rm.twcss', 'bg-green-400', $this->sp->id, $this->sp->user_id);
                Status::set('sp.links.running.add.twcss', 'bg-red-400', $this->sp->id, $this->sp->user_id);
            throw $e;
        }
        Status::set('sp.links.running.rm.twcss', 'bg-green-400', $this->sp->id, $this->sp->user_id);
        Status::set('sp.links.running.add.twcss', 'bg-red-400', $this->sp->id, $this->sp->user_id);
    }
}
