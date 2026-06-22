<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>

    {{-- SESSION --}}
    @if (session()->has('success'))
        <div x-data x-init="Flux.toast({ 
            text: '{{ session('success') }}', 
            heading: 'Success!', 
            variant: 'success' 
        })"></div>
    @endif

    {{-- FLUX UI --}}
    <flux:toast />
</x-layouts::app.sidebar>