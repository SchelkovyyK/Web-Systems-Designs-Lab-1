<?php

namespace App\Http\Controllers;

use App\Models\ShortLink;
use Illuminate\Http\RedirectResponse;

class RedirectController extends Controller
{
    public function __invoke(string $code): RedirectResponse
    {
        $shortLink = ShortLink::where('short_code', $code)->firstOrFail();

        $shortLink->increment('click_count');

        return redirect()->away($shortLink->original_url);
    }
}
