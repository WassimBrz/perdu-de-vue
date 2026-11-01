<?php

namespace App\Http\Controllers;

use App\Models\Place;
use Illuminate\View\View;
use Illuminate\Http\Request;

class PlaceController extends Controller
{
    public function show(Place $place): View
    {
        $items = $place->items()
            ->with(['place', 'tags'])
            ->latest('found_on')
            ->latest('id')
            ->get();

        return view('places.show', ['place' => $place, 'items' => $items]);
    }
}
