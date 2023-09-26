<div>

    {{-- Start Coding  --}}

    {{-- Canvas 
    Main BgColor / 
    Size H/W --}}
    <div class="w-full h-[calc(100vh-68px)] bg-white border border-gray-200 px-16 pt-2">

        {{-- Title  --}}
        <div class="mb-2">
            <h3 class="text-3xl font-bold text-gray-900 ">{{ $myTitle }}</h3>
            <span class="text-base font-normal text-gray-700">{{ $mySnipet }}</span>
        </div>
        {{-- Title --}}

        {{-- Top Bar --}}
        <div class="flex justify-between">

            <div class="flex w-full">
                {{-- Cari Data --}}
                <div class="relative w-1/2 mr-2 pointer-events-auto">
                    <div class="absolute inset-y-0 left-0 flex items-center p-5 pl-3 pointer-events-none ">
                        <svg width="24" height="24" fill="none" aria-hidden="true" class="flex-none mr-3 ">
                            <path d="m19 19-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"></path>
                            <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round"></circle>
                        </svg>
                    </div>

                    <x-text-input type="text" class="w-full p-2 pl-10" placeholder="Cari Data" autofocus
                        wire:model.live.debounce.2s="myTopBar.refSearch" />
                </div>
                {{-- Cari Data --}}

                {{-- Tanggal --}}
                <div class="relative w-1/6">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-900 dark:text-gray-400" fill="currentColor"
                            viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>

                    <x-text-input type="text" class="w-full p-2 pl-10 " placeholder="[dd/mm/yyyy]"
                        wire:model.live.debounce.2s="myTopBar.refDate" />
                </div>
                {{-- Tanggal --}}

                <x-primary-button class="ml-2" wire:click='scanLogProses()' wire:loading.remove>
                    {{ 'ScanLog' }}
                </x-primary-button>

                <div wire:loading wire:target="scanLogProses">
                    <x-loading />
                </div>

                <x-primary-button class="ml-2" wire:click='scanLogProses()' wire:loading.remove>
                    {{ 'ScanLog All' }}
                </x-primary-button>

            </div>

            {{-- Dropdown --}}
            <x-dropdown align="right" :width="__('48')">
                <x-slot name="trigger">
                    {{-- Button Dropdown Menu --}}
                    <x-primary-button class="inline-flex">
                        <svg class="-ml-1 mr-1.5 w-5 h-5" fill="currentColor" viewbox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                        </svg>
                        ({{ 'Tampil_1' }})
                    </x-primary-button>
                </x-slot>
                {{-- Open Dropdown Menu --}}
                <x-slot name="content">

                    <x-dropdown-link>
                        {{ __(1) }}
                    </x-dropdown-link>
                    <x-dropdown-link>
                        {{ __(2) }}
                    </x-dropdown-link>
                    <x-dropdown-link>
                        {{ __(3) }}
                    </x-dropdown-link>
                </x-slot>
            </x-dropdown>
            {{-- Dropdown --}}



        </div>
        {{-- Top Bar --}}






        <div class="h-[calc(100vh-250px)] mt-2 overflow-auto">
            <!-- Table -->
            <table class="w-full text-sm text-left text-gray-700 table-auto ">
                <thead class="sticky top-0 text-xs text-gray-900 uppercase bg-gray-100">
                    <tr>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            NIK
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Jam Hadir
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Status
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Tanggal
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white ">

                    @foreach ($myQueryData as $myQData)
                        <tr class="border-b ">
                            <td class="px-4 py-2">
                                {{ $myQData->emp_id }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->at_hour }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->at_mode }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->at_date }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>

        {{ $myQueryData->links() }}








    </div>

    {{-- Canvas 
    Main BgColor / 
    Size H/W --}}

    {{-- End Coding --}}
</div>
