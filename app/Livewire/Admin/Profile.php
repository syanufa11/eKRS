<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\WithBreadcrumbs;

class Profile extends Component
{
    public $name, $email;

    // Menentukan aturan validasi
    protected function rules()
    {
        return [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . Auth::id(),
        ];
    }

    // Custom pesan error dalam Bahasa Indonesia
    protected function messages()
    {
        return [
            'name.required'  => 'Nama lengkap wajib diisi.',
            'name.string'    => 'Format nama harus berupa teks.',
            'name.max'       => 'Nama jangan lebih dari 255 karakter.',
            'email.required' => 'Alamat email tidak boleh kosong.',
            'email.email'    => 'Format email-nya belum benar (contoh: nama@email.com).',
            'email.unique'   => 'Email ini sudah terdaftar, silakan gunakan email lain.',
        ];
    }

    // Fungsi sakti untuk validasi real-time saat mengetik
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->breadcrumb('Edit Profile');
    }

    public function updateProfile()
    {
        $this->validate();

        Auth::user()->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Profil berhasil diperbarui!']);
    }

    use WithBreadcrumbs;

    public function render()
    {
        return view('livewire.admin.profile-edit')
        ->layout('layouts.app', [
            'breadcrumbs' => $this->breadcrumbs,
        ])
            ->title('Edit Profile');;
    }
}
