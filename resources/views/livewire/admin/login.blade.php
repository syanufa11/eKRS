<div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-gray-900">
    <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-gray-900">
        <div class="flex w-full flex-1 flex-col lg:w-1/2">

            <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
                <div class="mb-5 sm:mb-8 text-center sm:text-start">
                    <h1 class="text-title-sm sm:text-title-md mb-2 font-semibold text-gray-800 dark:text-white/90">
                        Sign In
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Enter your email and password to sign in!
                    </p>
                </div>

                <form wire:submit="login">
                    @csrf
                    <div class="space-y-5">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Email<span class="text-red-500">*</span>
                            </label>
                            <input type="email"
                                wire:model.live="email"
                                placeholder="info@gmail.com"
                                class="h-11 w-full rounded-lg border bg-transparent px-4 py-2.5 text-sm focus:ring-3 focus:outline-none transition-all dark:bg-gray-900 dark:text-white/90 {{ $errors->has('email') ? 'border-red-500 focus:ring-red-500/20' : 'border-gray-300 dark:border-gray-700 focus:ring-brand-500/20' }}" />
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Password<span class="text-red-500">*</span>
                            </label>
                            <div x-data="{ showPassword: false }" class="relative">
                                <input :type="showPassword ? 'text' : 'password'"
                                    wire:model.live="password"
                                    placeholder="Enter your password"
                                    class="h-11 w-full rounded-lg border bg-transparent py-2.5 pr-11 pl-4 text-sm focus:ring-3 focus:outline-none transition-all dark:bg-gray-900 dark:text-white/90 {{ $errors->has('password') ? 'border-red-500 focus:ring-red-500/20' : 'border-gray-300 dark:border-gray-700 focus:ring-brand-500/20' }}" />

                                <span @click="showPassword = !showPassword"
                                    class="absolute top-1/2 right-4 z-30 -translate-y-1/2 cursor-pointer text-gray-500 dark:text-gray-400">
                                    <svg x-show="!showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M10 13.8619C7.233 13.8619 4.868 12.1372 3.923 9.70241C4.868 7.26761 7.233 5.54297 10 5.54297C12.766 5.54297 15.132 7.26762 16.077 9.70243C15.132 12.1372 12.766 13.8619 10 13.8619ZM10 4.04297C6.481 4.04297 3.494 6.30917 2.415 9.4593C2.361 9.61687 2.361 9.78794 2.415 9.94552C3.494 13.0957 6.481 15.3619 10 15.3619C13.518 15.3619 16.505 13.0957 17.584 9.94555C17.638 9.78797 17.638 9.6169 17.584 9.45932C16.505 6.30919 13.518 4.04297 10 4.04297Z" fill="#98A2B3" />
                                    </svg>
                                    <svg x-show="showPassword" class="fill-current" width="20" height="20" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M4.638 3.577C4.345 3.284 3.87 3.284 3.577 3.577C3.284 3.869 3.284 4.344 3.577 4.637L4.853 5.913C3.746 6.841 2.893 8.063 2.415 9.459C2.361 9.616 2.361 9.788 2.415 9.945C3.494 13.095 6.481 15.361 10 15.361C11.255 15.361 12.442 15.073 13.499 14.559L15.362 16.422C15.655 16.715 16.13 16.715 16.423 16.422C16.716 16.13 16.716 15.655 16.423 15.362L4.638 3.577Z" fill="#98A2B3" />
                                    </svg>
                                </span>
                            </div>
                            @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" wire:loading.attr="disabled"
                                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="login">Sign In</span>
                                <div wire:loading wire:target="login" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <div class="bg-brand-950 relative hidden h-full w-full items-center lg:grid lg:w-1/2 dark:bg-white/5">
            <div class="z-1 flex items-center justify-center">
                <x-common.common-grid-shape />
                <div class="flex max-w-xs flex-col items-center">
                    <a href="/" class="mb-4 flex items-center gap-1">
                        <span class="text-4xl font-black tracking-tight">
                            <span class="text-brand-500">e</span><span class="text-white">KRS</span>
                        </span>
                    </a>
                    <p class="text-center text-gray-400 dark:text-white/60">
                        Sistem Kartu Rencana Studi Elektronik
                    </p>
                </div>
            </div>
        </div>

        <div class="fixed right-6 bottom-6 z-50">
            <button class="bg-brand-500 hover:bg-brand-600 inline-flex size-14 items-center justify-center rounded-full text-white transition-colors" @click.prevent="$store.theme.toggle()">
                <svg class="hidden dark:block fill-current" width="20" height="20" viewBox="0 0 20 20">
                    <path d="M10 1.5v2M10 15.7v2M15.9 5L14.9 6M18.4 10h-2M15.9 15L14.9 14M5.9 15L4.9 16M4.2 10H2.2M5.9 5L4.9 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                <svg class="dark:hidden fill-current" width="20" height="20" viewBox="0 0 20 20">
                    <path d="M17.4 11.9a7.5 7.5 0 1 1-9.4-9.4 7.5 7.5 0 0 0 9.4 9.4z" />
                </svg>
            </button>
        </div>
    </div>
</div>
