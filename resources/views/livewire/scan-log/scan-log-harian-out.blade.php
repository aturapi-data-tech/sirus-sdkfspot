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
                <div class="relative w-1/3 mr-2 pointer-events-auto">
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
                <div class="relative w-1/6 mr-2">
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

                {{-- Shift --}}
                <div class="relative w-1/12">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-800 dark:text-white" aria-hidden="true"
                            xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path
                                d="M1 5h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 1 0 0-2H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2Zm18 4h-1.424a3.228 3.228 0 0 0-6.152 0H1a1 1 0 1 0 0 2h10.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Zm0 6H8.576a3.228 3.228 0 0 0-6.152 0H1a1 1 0 0 0 0 2h1.424a3.228 3.228 0 0 0 6.152 0H19a1 1 0 0 0 0-2Z" />
                        </svg>
                    </div>

                    <x-text-input type="text" class="w-full p-2 pl-10 " placeholder="[Shift 1/2/3]"
                        wire:model.live.debounce.2s="myTopBar.refShift" />
                </div>
                {{-- Shift --}}

                {{-- <x-primary-button class="ml-2" wire:click='scanLogProses()' wire:loading.remove>
                    {{ 'ScanLog' }}
                </x-primary-button>

                <div wire:loading wire:target="scanLogProses">
                    <x-loading />
                </div>

                <x-primary-button class="ml-2" wire:click='scanLogProses()' wire:loading.remove>
                    {{ 'ScanLog All' }}
                </x-primary-button> --}}

            </div>

            <div>
                {{-- <x-primary-button class="ml-2" wire:click='userProses()' wire:loading.remove>
                    {{ 'User' }}
                </x-primary-button>

                <div wire:loading wire:target="userProses">
                    <x-loading />
                </div> --}}

                {{-- <x-primary-button class="ml-2" wire:click='getDevInfoMachine()' wire:loading.remove>
                    {{ 'DevieInfo' }}
                </x-primary-button>

                <div wire:loading wire:target="getDevInfoMachine">
                    <x-loading />
                </div> --}}
            </div>

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
                            Nama
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Jabatan
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Keterangan
                        </th>
                        <th scope="col" class="w-1/4 px-4 py-3 ">
                            Jam Hadir
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
                                {{ $myQData->emp_name }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->emp_jabatan }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->emp_keterangan }}
                            </td>
                            <td class="px-4 py-2">
                                {{ $myQData->at_hour_o }}
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
