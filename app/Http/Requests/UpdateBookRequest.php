<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateBookRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     * Hanya pengguna dengan peran admin yang diizinkan (SEC-08 / FR-005).
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'min:1800', 'max:' . (date('Y') + 1)],
            'category' => ['required', 'string', 'max:100'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000'],
            'is_archived' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Pesan validasi kustom dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Nama pengarang wajib diisi.',
            'year.required' => 'Tahun terbit wajib diisi.',
            'year.integer' => 'Tahun terbit harus berupa angka.',
            'category.required' => 'Kategori buku wajib diisi.',
            'stock.required' => 'Jumlah stok buku wajib diisi.',
            'stock.min' => 'Stok buku tidak boleh kurang dari 0.',
        ];
    }
}
