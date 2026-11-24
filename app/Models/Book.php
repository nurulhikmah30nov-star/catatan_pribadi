<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'author',
        'year',
        'category',
        'stock',
        'is_archived',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'stock' => 'integer',
            'is_archived' => 'boolean',
        ];
    }

    /**
     * Relasi hasMany dengan riwayat peminjaman (Loans).
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Relasi hasMany dengan ulasan buku (Reviews).
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
