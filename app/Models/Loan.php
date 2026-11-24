<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'loan_date',
        'due_date',
        'returned_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loan_date' => 'date',
            'due_date' => 'date',
            'returned_at' => 'datetime',
        ];
    }

    /**
     * Relasi belongsTo dengan model User (peminjam).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi belongsTo dengan model Book (buku yang dipinjam).
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
