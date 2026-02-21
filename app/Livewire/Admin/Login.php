<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Rule;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;

class Login extends Component
{
    #[Rule('required', message: 'Email tidak boleh kosong.')]
    #[Rule('email', message: 'Format email tidak valid.')]
    public string $email = '';

    #[Rule('required', message: 'Kata sandi wajib diisi.')]
    #[Rule('min:6', message: 'Kata sandi minimal 6 karakter.')]
    public string $password = '';

    public bool $remember = false;

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function login()
    {
        $credentials = $this->validate();

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'Kredensial tidak cocok.');
            return;
        }

        session()->put([
            'is_admin'   => true,
            'admin_id'   => auth()->id(),
            'admin_name' => auth()->user()->name,
        ]);

        $this->js("window.location.href = '" . route('dashboard') . "?welcome=1'");
    }

    public function render()
    {
        return view('livewire.admin.login')
            ->extends('layouts.fullscreen-layout')->title('Login');
    }
}
