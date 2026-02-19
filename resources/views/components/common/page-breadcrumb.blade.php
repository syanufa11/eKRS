@props([
'items' => [],
])

@php
$last = collect($items)->last();
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">
        {{ $last['label'] ?? 'Page' }}
    </h2>

    <nav>
        <ol class="flex items-center gap-1.5">
            @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                @if (!empty($item['url']))
                <a
                    href="{{ $item['url'] }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                    {{ $item['label'] }}
                </a>
                @else
                <span class="text-sm text-gray-800 dark:text-white/90">
                    {{ $item['label'] }}
                </span>
                @endif

                {{-- Arrow hanya kalau bukan terakhir --}}
                @unless($loop->last)
                <svg
                    class="stroke-current text-gray-400"
                    width="17"
                    height="16"
                    viewBox="0 0 17 16"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M6.0765 12.667L10.2432 8.50033L6.0765 4.33366"
                        stroke-width="1.2"
                        stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                @endunless
            </li>
            @endforeach
        </ol>
    </nav>
</div>
