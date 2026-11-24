@if (session('status') || session('success'))
    <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-900 shadow-sm flex items-center space-x-3" role="alert">
        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600 flex-shrink-0" />
        <span class="font-medium">{{ session('status') ?? session('success') }}</span>
    </div>
@endif

@if (session('notice'))
    <div class="mb-6 p-4 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-900 shadow-sm flex items-center space-x-3" role="alert">
        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 flex-shrink-0" />
        <span class="font-medium">{{ session('notice') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-900 shadow-sm flex items-center space-x-3" role="alert">
        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 flex-shrink-0" />
        <span class="font-medium">{{ session('error') }}</span>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 text-sm text-red-900 shadow-sm space-y-1" role="alert">
        <div class="flex items-center space-x-2 font-semibold">
            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-red-600 flex-shrink-0" />
            <span>Terdapat beberapa kesalahan input:</span>
        </div>
        <ul class="list-disc list-inside pl-5 space-y-0.5 text-xs text-red-800">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
