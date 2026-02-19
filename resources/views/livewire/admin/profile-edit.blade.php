<x-common.component-card title="Edit Profile">
    <form wire:submit.prevent="updateProfile" class="space-y-6">
        {{-- Input Nama --}}
        {{-- Input Nama --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Nama Lengkap
            </label>
            <input type="text" wire:model.live.debounce.500ms="name"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border @error('name') border-red-500 @else border-gray-300 dark:border-gray-700 @enderror bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:text-white/90 dark:placeholder:text-white/30" />
            @error('name') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        {{-- Input Email --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                Alamat Email
            </label>
            <input type="email" wire:model.live.debounce.500ms="email" placeholder="contoh@email.com"
                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border @error('email') border-red-500 @else border-gray-300 dark:border-gray-700 @enderror bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:text-white/90 dark:placeholder:text-white/30" />
            @error('email') <span class="mt-1 text-xs text-red-500">{{ $message }}</span> @enderror
        </div>

        {{-- Tombol Submit --}}
        <div class="flex justify-end shadow-theme-xs">
            <button type="submit"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-500/20">
                Simpan Perubahan
            </button>
        </div>
    </form>
</x-common.component-card>
