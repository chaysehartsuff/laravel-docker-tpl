@props([
    'scraper',
])

<div>
    <form action="{{ route('preference.update') }}" method="POST">
        @csrf
        <input type="hidden" name="id" value="{{ $scraper->id }}">
        
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
            <input type="text" name="name" id="name" value="{{ $scraper->name }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
        </div>
        
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">{{ $scraper->description }}</textarea>
        </div>
        
        <div class="flex items-center justify-end mt-4">
            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:hover:bg-blue-700 dark:focus:ring-offset-gray-800">
                Update
            </button>
        </div>
    </form>

    <x-widget title="Advanced" class="mt-2 mx-auto px-2">

        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
            <form action="{{ route('scraper.run', ['scraperPreference' => $scraper->id]) }}" method="POST" data-ajax class="flex items-center space-x-4">
                @csrf
                <div class="flex items-center space-x-2">
                    <label for="find_urls" class="text-sm font-medium text-gray-700 dark:text-gray-300">Find</label>
                    <input type="hidden" name='name' value="sp.links" />
                    <input type="number" name="url_count" id="url_count" min="1" value="1" class="w-16 px-2 py-1 text-sm border-gray-300 dark:border-gray-700 rounded-md focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">urls</span>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Run
                    </button>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-gray-800 dark:text-gray-200">
                        <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 8 8">
                            <circle cx="4" cy="4" r="3" />
                        </svg>
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Ready to run</span>
                </div>
            </form>
        </div>
    </x-widget>
</div>