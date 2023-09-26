<div>
    {{-- Start Coding --}}
    <div>
        <div>
            <h1 class="m-2 italic font-extrabold text-center text-8xl bg-slate-400">{{ $count }}</h1>
        </div>
        <div class="flex items-center justify-center">
            <x-primary-button class="m-2" wire:click="increment">+</x-primary-button>
            <x-primary-button class="m-2" wire:click="decrement">-</x-primary-button>
        </div>
    </div>
    {{-- End Coding --}}


</div>
