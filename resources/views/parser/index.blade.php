<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('parser.page_title') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-12">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">{{ __('parser.upload_heading') }}</h1>
            
            <form action="{{ route('names.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <input type="file" name="file" id="file" class="hidden" accept=".csv">
                    <label for="file" class="cursor-pointer inline-flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <span class="text-blue-600 font-medium">{{ __('parser.select_file') }}</span>
                        <span class="text-gray-500 text-sm">{{ __('parser.drag_hint') }}</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                    {{ __('parser.process_button') }}
                </button>

                @error('file')
                    <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
                        {{ $message }}
                    </div>
                @enderror
            </form>

            @if(isset($success))
                <div class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-sm">
                    {{ __($success) }}
                </div>
            @endif
        </div>

        @if(isset($results) && count($results) > 0)
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-left">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">{{ __('parser.table_title') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">{{ __('parser.table_first_name') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">{{ __('parser.table_initial') }}</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">{{ __('parser.table_last_name') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($results as $dto)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $dto->title ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $dto->first_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $dto->initial ?? '—' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $dto->last_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('file').onchange = function() {
            if(this.files[0]) {
                const label = this.nextElementSibling;
                label.querySelector('.text-blue-600').textContent = this.files[0].name;
            }
        };
    </script>
</body>
</html>