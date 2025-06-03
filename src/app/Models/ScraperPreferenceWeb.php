<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScraperPreferenceWeb extends Model
{
    protected $fillable = ['url', 'content', 'scraper_preference_id', 'last_scraped_at'];

    /**
     * Check if a URL exists for a given ScraperPreference.
     *
     * @param ScraperPreference $scraperPreference
     * @param string $url
     * @return bool
     */
    public static function exists(ScraperPreference $scraperPreference, string $url): bool
    {
        return self::where('scraper_preference_id', $scraperPreference->id)
                   ->where('url', $url)
                   ->exists();
    }
}
