<?php

namespace App\Http\Controllers;

use App\Enums\Color;
use App\Http\Requests\ItemRequest;
use App\Models\Item;
use App\Models\Place;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $place = Place::find($request->integer('lieu'));
        $color = Color::tryFrom($request->string('couleur')->toString());
        $search = $request->string('q')->trim()->toString();

        $items = Item::query()
            ->whereNull('returned_at')
            ->with(['place', 'tags'])
            ->when($place, fn ($query) => $query->whereBelongsTo($place))
            ->when($color, fn ($query) => $query->where('color', $color))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest('found_on')
            ->latest('id')
            ->paginate(9)
            ->withQueryString();

        return view('items.index', [
            'items' => $items,
            'places' => Place::orderBy('name')->get(),
            'selectedPlace' => $place,
            'selectedColor' => $color,
            'search' => $search,
        ]);
    }

     public function create(): View
    {
        return view('items.create', $this->formData(new Item));
    }

    public function store(ItemRequest $request): RedirectResponse
    {
        $item = Item::create($request->safe()->except('tags'));
        $item->tags()->sync($request->validated('tags') ?? []);

        return redirect()->route('items.show', $item)->with('status', 'L\'objet a été ajouté.');
    }

    public function show(Item $item): View
    {
        return view('items.show', ['item' => $item->load(['place', 'tags'])]);
    }

    public function edit(Item $item): View
    {
        return view('items.edit', $this->formData($item->load('tags')));
    }

    public function update(ItemRequest $request, Item $item): RedirectResponse
    {
        $item->update($request->safe()->except('tags'));
        $item->tags()->sync($request->validated('tags') ?? []);

        return redirect()->route('items.show', $item)->with('status', 'L\'objet a été modifié.');
    }

    public function return(Item $item): RedirectResponse
    {
        if ($item->isReturned()) {
            return redirect()->route('items.show', $item)
                ->with('status', 'Cet objet a déjà été restitué le '.$item->returned_at->Format('d/m/Y').'.');
        }

        $item->update(['returned_at' => today()]);

        return redirect()->route('items.index')->with('status', $item->name.' a retrouvé son propriétaire.');
    }

     public function destroy(Item $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('items.index')->with('status', 'L\'objet a été supprimé.');
    }

     private function formData(Item $item): array
    {
        return [
            'item' => $item,
            'places' => Place::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ];
    }

}
