<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\Color;
use Database\Factories\ItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['place_id', 'name', 'description', 'color', 'found_on', 'returned_at'])]
class Item extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'color' => Color::class,
            'found_on' => 'date',
            'returned_at' => 'date',
        ];
    }

    public function place(): BelongsTo
    {
        return $this->belongsTo(Place::class);
    }
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function isReturned(): bool
    {
        return $this->returned_at !== null;
    }

}
