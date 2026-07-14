<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'theme_preference' => ['required', 'in:day,night,system'],
        ]);

        $request->user()->update([
            'theme_preference' => $data['theme_preference'],
        ]);

        return back();
    }
}
