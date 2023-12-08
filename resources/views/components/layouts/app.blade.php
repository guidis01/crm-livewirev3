<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>
        @vite(['resources/css/app.css', 'resources/css/app.js'])
    </head>
    <body class="min-h-screen font-sans antialiased">
<x-nav sticky class="lg:hidden bg-sky-800 text-white">
    <x-slot:brand>
        {{-- Drawer toggle for "main-drawer" --}}
        <label for="main-drawer" class="lg:hidden mr-3">
            <x-icon name="o-bars-3" class="cursor-pointer" />
        </label>

        {{-- Your logo --}}
        My App
    </x-slot:brand>
</x-nav>

<x-main>
    <x-slot:sidebar drawer="main-drawer" collapsible class="pt-3 bg-sky-800 text-white">

            <div class="hidden-when-collapsed ml-5 font-black text-4xl text-yellow-500">mary</div>

            <div class="display-when-collapsed ml-5 font-black text-4xl text-orange-500">m</div>

            <x-menu activate-by-route active-bg-color="bg-base-300/10">

                @if($user = auth()->user())
                    <x-list-item :item="$user" sub-value="username" no-separator no-hover class="!-mx-2 mt-2 mb-5 border-y border-y-sky-900">
                        <x-slot:actions>
                            <x-button icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logoff" />
                        </x-slot:actions>
                    </x-list-item>
                @endif

                <x-menu-item title="Home" icon="o-home" link="/" />
                <x-menu-item title="Yeah" icon="o-sparkles" link="####" />

                <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                    <x-menu-item title="Wifi" icon="o-wifi" />
                    <x-menu-item title="Archives" icon="o-archive-box" />
                </x-menu-sub>
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>
    </body>

</html>
