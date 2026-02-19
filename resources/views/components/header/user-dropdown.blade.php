<div class="relative" x-data="{
    dropdownOpen: false,
    toggleDropdown() {
        this.dropdownOpen = !this.dropdownOpen;
    },
    closeDropdown() {
        this.dropdownOpen = false;
    },
    confirmLogout() {
    Swal.fire({
        title: 'Apakah anda yakin?',
        text: 'Anda akan keluar dari sesi ini.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Keluar!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            this.$refs.logoutButton.closest('form').submit();
        }
    });
}
}" @click.away="closeDropdown()">

    <button
        class="flex items-center text-gray-700 dark:text-gray-400 focus:outline-none group"
        @click.prevent="toggleDropdown()"
        type="button">

        <span class="mr-3 flex items-center justify-center rounded-full h-10 w-10 border border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-white/5 group-hover:bg-gray-100 dark:group-hover:bg-white/10 transition-colors">
            <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
            </svg>
        </span>

        <span class="block mr-1 font-medium text-theme-sm">{{ Auth::user()->name }}</span>

        <svg
            class="w-5 h-5 transition-transform duration-200 text-gray-400"
            :class="{ 'rotate-180': dropdownOpen }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <div
        x-show="dropdownOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        class="absolute right-0 mt-3 flex w-[260px] flex-col rounded-2xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-800 dark:bg-gray-900 z-50">

        <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-800 pb-3 mb-1">
            <span class="block font-semibold text-gray-800 text-theme-sm dark:text-white/90 truncate">
                {{ Auth::user()->name }}
            </span>
            <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400 truncate">
                {{ Auth::user()->email }}
            </span>
        </div>

        <ul class="flex flex-col gap-1 py-1">
            @php
            $menuItems = [
            [
            'text' => 'Edit Profil',
            'icon' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>',
            'route' => 'admin.profile',
            'active' => request()->routeIs('admin.profile'),
            ],
            [
            'text' => 'Ubah Kata Sandi',
            'icon' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>',
            'route' => 'admin.password',
            'active' => request()->routeIs('admin.password'),
            ],
            ];
            @endphp
            
            @foreach ($menuItems as $item)
            <li>
                <a
                    href="{{ route($item['route'], $item['params'] ?? []) }}"
                    class="flex items-center gap-3 px-3 py-2 font-medium rounded-lg group text-theme-sm transition-colors
            {{ $item['active']
                ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400'
                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-gray-300'
            }}">

                    <span class="{{ $item['active'] ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-400 group-hover:text-indigo-600 dark:group-hover:text-indigo-400' }}">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            {!! $item['icon'] !!}
                        </svg>
                    </span>
                    {{ $item['text'] }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="mt-1 pt-1 border-t border-gray-100 dark:border-gray-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    @click.prevent="confirmLogout()"
                    x-ref="logoutButton"
                    class="flex items-center w-full gap-3 px-3 py-2 font-medium text-gray-700 rounded-lg group text-theme-sm hover:bg-red-50 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-500/10 dark:hover:text-red-500 transition-colors">
                    <span class="text-gray-400 group-hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </span>
                    Sign out
                </button>
            </form>

        </div>
    </div>
</div>
