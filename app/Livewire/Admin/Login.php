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
        DB::beginTransaction();

        try {
            $credentials = $this->validate();

            if (!Auth::attempt($credentials, $this->remember)) {
                throw ValidationException::withMessages([
                    'email' => 'Kredensial tidak cocok.',
                ]);
            }

            session()->regenerate();

            session()->put([
                'is_admin'   => true,
                'admin_id'   => auth()->id(),
                'admin_name' => auth()->user()->name,
            ]);

            DB::commit();

            $this->dispatch(
                'notify',
                message: 'Selamat datang, ' . auth()->user()->name . '!',
                type: 'success'
            );

            return redirect()->intended(route('dashboard'));
        } catch (\Throwable $e) {
            DB::rollBack();

            report($e);

            $this->dispatch(
                'notify',
                message: 'Email atau kata sandi salah!',
                type: 'error'
            );

            throw ValidationException::withMessages([
                'email' => 'Kredensial tidak cocok.',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.admin.login')
            ->extends('layouts.fullscreen-layout')->title('Login');
    }
}
