<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreBookRequest extends FormRequest
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
            'title.max' => 'Judul buku maksimal 255 karakter.',
            'author.required' => 'Nama penulis/pengarang wajib diisi.',
            'author.max' => 'Nama pengarang maksimal 255 karakter.',
            'year.required' => 'Tahun terbit wajib diisi.',
            'year.integer' => 'Tahun terbit harus berupa angka.',
            'year.min' => 'Tahun terbit minimal tahun 1800.',
            'year.max' => 'Tahun terbit tidak boleh melebihi tahun depan.',
            'category.required' => 'Kategori buku wajib diisi.',
            'category.max' => 'Kategori buku maksimal 100 karakter.',
            'stock.required' => 'Jumlah stok buku wajib diisi.',
            'stock.integer' => 'Stok buku harus berupa bilangan bulat.',
            'stock.min' => 'Stok buku tidak boleh bernilai negatif.',
            'stock.max' => 'Stok buku maksimal 1000 eksemplar per judul.',
        ];
    }
}
