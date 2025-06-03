@props([
    'title' => 'Widget Title',
    'status' => 'default',
    'headerBg' => 'bg-gray-100 dark:bg-gray-700',
    'bodyBg' => 'bg-white dark:bg-gray-800',
    'scraper' => null,
])

@php
$attributes = $attributes->class([
    'w-full mb-6 rounded-xl shadow-md overflow-hidden transition-all duration-300'
])->merge(['x-data' => '{ open: false }']);
@endphp

<div {{ $attributes }}>
    <input type="hidden" class="bg-gray-400 bg-red-400 bg-green-400" />
    <div class="flex justify-between items-center px-5 py-4 border-b border-gray-200 dark:border-gray-600 {{ $headerBg }}">
        <div class="flex items-center space-x-3">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                {{ $title }}
            </h4>
            <div class="flex items-center">
                <span class="w-3 h-3 rounded-full {{ $statusColor ?? 'bg-gray-400'}}" 
                    status-code-add-class="sp.links.running.add.twcss"
                    status-code-remove-class="sp.links.running.rm.twcss"
                    status-meta-id="{{ $scraper->id ?? '' }}"></span>
            </div>
        </div>
        <button @click="open = !open" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-transform duration-200"
            :class="{ 'rotate-90': open }">
            <svg class="w-5 h-5 transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>
    <div x-show="open" x-collapse class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300 {{ $bodyBg }}">
        {{ $slot }}
    </div>
</div>