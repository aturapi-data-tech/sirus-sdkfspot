<?php

namespace App\Livewire\ScanLog;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;
use Carbon\Carbon;

use Livewire\Component;


class ScanLogHarianOut extends Component
{
    use WithPagination;

    // primitive Variable
    public string $myTitle = 'Absensi Pulang';
    public string $mySnipet = 'Data Absensi Hari ini ';

    // TopBar
    public array $myTopBar = [
        'refDate' => '',
        'refSearch' => '',
        'refShift' => ''
    ];


    // shift control
    private function setShiftnCurrentDate(): void
    {
        // dd/mm/yyyy hh24:mi:ss
        $this->myTopBar['refDate'] = Carbon::now()->format('d/m/Y');
        // dd(Carbon::now()->format('H:i:s'));

        // shift
        $findShift = DB::table('rstxn_shiftctls')->select('shift')
            ->whereRaw("'" . Carbon::now()->format('H:i:s') . "' between
            shift_start and shift_end")
            ->first();
        $this->myTopBar['refShift'] = isset($findShift->shift) && $findShift->shift ? $findShift->shift : 3;
    }



    public function mount()
    {
        // Set TopBar
        $this->setShiftnCurrentDate();
    }

    public function render()
    {
        // set mySearch
        $mySearch = $this->myTopBar['refSearch'];
        $myRefdate = $this->myTopBar['refDate'];
        $myRefshift = $this->myTopBar['refShift'];

        // myQuery  /Collection
        $myQueryData = DB::table('abview_cekouts')
            ->select(
                'at_hour_o',
                'at_date_o',
                'at_mode',
                'emp_id',
                'emp_name',
                'emp_jabatan',
                'emp_keterangan',
            )
            ->where(DB::raw("to_char(at_date_o,'dd/mm/yyyy')"), '=', $myRefdate)
            ->where("shift_id", '=', $myRefshift);

        $myQueryData->where(function ($q) use ($mySearch) {
            $q->orWhere(DB::raw('upper(emp_id)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_name)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_jabatan)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_keterangan)'), 'like', '%' . strtoupper($mySearch) . '%');
        })

            ->orderBy('emp_id', 'asc');
        // myQuery


        return view(
            'livewire.scan-log.scan-log-harian-out',
            ['myQueryData' => $myQueryData->paginate(20)]
        );
    }
}
