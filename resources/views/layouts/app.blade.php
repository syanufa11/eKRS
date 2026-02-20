<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    class="h-full"
    x-data :class="$store.theme.theme === 'dark' ? 'dark' : ''">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">

    <style>
        /* Terkadang TomSelect butuh penyesuaian tinggi di dalam grid */
        .ts-control {
            border-radius: 0.75rem !important;
            padding: 0.75rem !important;
            border: 1px solid #e2e8f0 !important;
            background-color: #f8fafc !important;
        }

        .ts-dropdown {
            z-index: 99999 !important;
            /* Agar di depan modal */
        }

        .overflow-hidden {
            overflow: hidden;
        }
    </style>
    <title>{{ $title ?? '' }} | eKRS</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    <script>
        // Logika pencegahan flash (hanya untuk <html> karena <body> belum ada)
        (function() {
            const theme = localStorage.getItem('theme') ||
                (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();

        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                theme: localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'),
                toggle() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                    localStorage.setItem('theme', this.theme);
                }
            });

            Alpine.store('sidebar', {
                isExpanded: window.innerWidth >= 1280,
                isMobileOpen: false,
                isHovered: false,
                toggleExpanded() {
                    this.isExpanded = !this.isExpanded;
                    this.isMobileOpen = false;
                },
                toggleMobileOpen() {
                    this.isMobileOpen = !this.isMobileOpen;
                },
                setMobileOpen(val) {
                    this.isMobileOpen = val;
                },
                setHovered(val) {
                    if (window.innerWidth >= 1280 && !this.isExpanded) {
                        this.isHovered = val;
                    }
                }
            });
        });
    </script>
</head>

<body
    x-data="{ loaded: true, isModalOpen: false }"
    class="bg-white dark:bg-gray-900 text-gray-800 dark:text-white/90"
    :class="{ 'overflow-hidden': isModalOpen }"
    @open-modal.window="isModalOpen = true"
    @close-modal.window="isModalOpen = false"
    @resize.window="
        if (window.innerWidth < 1280) {
            $store.sidebar.setMobileOpen(false);
            $store.sidebar.isExpanded = false;
        } else {
            $store.sidebar.isMobileOpen = false;
            $store.sidebar.isExpanded = true;
        }
    ">

    <x-common.preloader />

    <div class="min-h-screen xl:flex">
        @include('layouts.backdrop')
        @include('layouts.sidebar')

        <div class="flex-1 transition-all duration-300 ease-in-out"
            :class="{
                'xl:ml-[290px]': $store.sidebar.isExpanded || $store.sidebar.isHovered,
                'xl:ml-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
                'ml-0': $store.sidebar.isMobileOpen
            }">

            @include('layouts.app-header')

            <div class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
                @if (!empty($breadcrumbs))
                <x-common.page-breadcrumb :items="$breadcrumbs" />
                @endif

                <div class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] lg:p-6">

                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        // 1. Sound Management
        window.clickSound = new Audio('{{ asset("sounds/click.mp3") }}');
        window.clickSound.volume = 0.3;

        window.playClickSound = function() {
            const audio = window.clickSound.cloneNode();
            audio.volume = 0.15;
            audio.play().catch(() => {});
        }

        // Global Click Listener
        document.addEventListener('click', function(e) {
            const target = e.target.closest('button, a[href], [role="button"]');
            if (target && !target.disabled) {
                window.playClickSound();
            }
        }, true);
    </script>

    @include('layouts.flash')

</body>

</html>
