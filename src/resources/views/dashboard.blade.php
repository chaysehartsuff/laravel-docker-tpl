<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Widgets') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ open: false }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8 flex justify-between items-center">
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Your Scrapers</h3>
                <button @click="open = true" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Create Scraper +
                </button>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($scrapers->count() > 0)
                        @foreach ($scrapers as $scraper)
                            <x-widget title="{{ $scraper->name }}" :scraper="$scraper">
                                @php
                                    $widgetContent = config("scrapers.{$scraper->type}.widget_view", 'default-widget-content');
                                @endphp
                                <x-dynamic-component :component="$widgetContent" :scraper="$scraper" />
                            </x-widget>     
                        @endforeach                  
                    @else
                        <p>No scrapers yet bud <span class="ml-1">¯\_(ツ)_/¯</span></p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div x-show="open" class="fixed inset-0 overflow-y-auto z-50" x-cloak>
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
                </div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form action="{{ route('scraper-preferences.store') }}" method="POST">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">Create New Scraper</h3>
                            @csrf
                            <div class="mb-4">
                                <label for="scraper-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Type
                                </label>
                                <select id="scraper-select" name="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-indigo-500 dark:focus:border-indigo-600 sm:text-sm rounded-md"> 
                                    @foreach(config('scrapers') as $key => $scraper)
                                        <option value="{{ $key }}">{{ $scraper['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-4">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Name
                                </label>
                                <input type="text" id="name" name="name" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-indigo-500 dark:focus:border-indigo-600 sm:text-sm rounded-md">
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea id="description" name="description" rows="3" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-indigo-500 dark:focus:ring-indigo-600 focus:border-indigo-500 dark:focus:border-indigo-600 sm:text-sm rounded-md"></textarea>
                            </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Create
                        </button>
                        <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.addEventListener('submit', function(e) {
                const form = e.target.closest('form[data-ajax]');
                if (form) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const method = form.method.toUpperCase();
                    const url = form.action;

                    fetch(url, {
                        method: method,
                        body: method === 'GET' ? null : formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);

                        const callback = form.getAttribute('data-ajax-callback');
                        if (callback && typeof window[callback] === 'function') {
                            window[callback](data);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                    });
                }
            });
        });

        function updateStatuses() {
            const types = ['html', 'id', 'add-class', 'remove-class'];

            fetch("{{ route('scraper.status') }}", {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.statuses && Array.isArray(data.statuses)) {
                    data.statuses.forEach(status => {
                        types.forEach(type => {
                            document.querySelectorAll(`[status-code-${type}]`).forEach(element => {
                                const statusCode = element.getAttribute(`status-code-${type}`);
                                const metaId = element.getAttribute('status-meta-id');

                                if (status.code === statusCode && (!metaId || metaId === status.meta_id)) {
                                    switch (type) {
                                        case 'html':
                                            element.innerHTML = status.content;
                                            break;
                                        case 'id':
                                            element.id = status.content;
                                            break;
                                        case 'add-class':
                                            element.classList.add(status.content);
                                            break;
                                        case'remove-class':
                                            element.classList.remove(status.content);
                                        break;
                                    }
                                }
                            });
                        });
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Start the status update loop
        setInterval(updateStatuses, 3000);
    </script>
    @endpush
</x-app-layout>