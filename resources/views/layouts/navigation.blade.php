<header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-xs">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Brand Logo -->
            <div class="flex items-center space-x-6">
                <a href="{{ url('/') }}" class="flex items-center space-x-2.5 text-gray-900 group">
                    <span class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 group-hover:bg-emerald-100 transition">
                        <x-heroicon-o-document-text class="w-6 h-6" />
                    </span>
                    <span class="text-lg font-bold tracking-tight text-gray-900">{{ config('app.name', 'CatatanSecure') }}</span>
                </a>

                @auth
                    <nav class="hidden md:flex items-center space-x-1" aria-label="Navigasi Utama">
                        <a href="{{ route('dashboard') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-50 text-emerald-800 font-semibold' : 'text-gray-700 hover:text-emerald-700 hover:bg-gray-50' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('notes.index') }}"
                           class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('notes.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : 'text-gray-700 hover:text-emerald-700 hover:bg-gray-50' }}">
                            Catatan Pribadi
                        </a>
                        @if (Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.dashboard') ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-gray-700 hover:text-amber-700 hover:bg-gray-50' }}">
                                Admin Panel
                            </a>
                            <a href="{{ route('admin.notes.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.notes.*') ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-gray-700 hover:text-amber-700 hover:bg-gray-50' }}">
                                Semua Catatan
                            </a>
                            <a href="{{ route('admin.users.index') }}"
                               class="px-3 py-2 rounded-md text-sm font-medium transition {{ request()->routeIs('admin.users.*') ? 'bg-amber-50 text-amber-800 font-semibold' : 'text-gray-700 hover:text-amber-700 hover:bg-gray-50' }}">
                                Kelola Pengguna
                            </a>
                        @endif
                    </nav>
                @endauth
            </div>

            <!-- Right Nav / User Profile & Auth -->
            <div class="flex items-center space-x-3">
                @auth
                    <div class="flex items-center space-x-2 pl-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ Auth::user()->role === 'admin' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                            @if (Auth::user()->role === 'admin')
                                <x-heroicon-s-shield-check class="w-3.5 h-3.5 mr-1" />
                                Administrator
                            @else
                                <x-heroicon-o-user class="w-3.5 h-3.5 mr-1" />
                                Member
                            @endif
                        </span>
                        <span class="text-sm font-medium text-gray-800 hidden sm:inline">{{ Auth::user()->name }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                title="Keluar dari Sesi"
                                class="inline-flex items-center px-2.5 py-1.5 border border-gray-200 rounded-lg text-xs font-medium text-gray-600 hover:text-red-700 hover:bg-red-50 hover:border-red-200 transition focus:outline-none focus:ring-2 focus:ring-red-500">
                            <x-heroicon-o-arrow-left-on-rectangle class="w-4 h-4 mr-1 text-red-500" />
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-700 px-3 py-1.5 rounded-lg transition">Masuk</a>
                    <a href="{{ route('register') }}" class="text-sm font-medium bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition shadow-xs focus:outline-none focus:ring-2 focus:ring-emerald-500">Daftar</a>
                @endauth
            </div>
        </div>
    </div>
</header>
