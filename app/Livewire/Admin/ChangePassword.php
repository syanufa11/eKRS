<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Traits\WithBreadcrumbs;

class ChangePassword extends Component
{
    public $current_password, $password, $password_confirmation;

    // Menggunakan method rules agar Password::min(6) bisa dieksekusi dengan dinamis
    protected function rules()
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ];
    }

    protected function messages()
    {
        return [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'current_password.current_password' => 'Kata sandi lama yang anda masukkan salah.',
            'password.required' => 'Kata sandi baru tidak boleh kosong.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal harus 6 karakter.',
        ];
    }

    public function updated($propertyName)
    {
        // PENTING: Panggil $this->rules() dan $this->messages() di dalam validateOnly
        $this->validateOnly($propertyName, $this->rules(), $this->messages());
    }

    public function updatePassword()
    {
        // PENTING: Panggil $this->rules() dan $this->messages() di dalam validate
        $this->validate($this->rules(), $this->messages());

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'password' => Hash::make($this->password)
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kata sandi berhasil diganti.'
        ]);
    }

    use WithBreadcrumbs;

    public function mount()
    {
        $this->breadcrumb('Change Password');
    }

    public function render()
    {
        return view('livewire.admin.change-password')
            ->layout('layouts.app', [
                'breadcrumbs' => $this->breadcrumbs,
            ])
            ->title('Change Password');;
    }
}
