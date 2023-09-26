<?php

namespace App\Livewire\ScanLog;

use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Carbon\Carbon;

use Livewire\Component;

class ScanLogHarian extends Component
{
    use WithPagination;

    // primitive Variable
    public string $myTitle = 'Absensi';
    public string $mySnipet = 'Data Absensi Hari ini ';

    // TopBar
    public array $myTopBar = [
        'refDate' => '',
        'refSearch' => '',
    ];


    // resetPage When refSearch is Typing
    public function updatedMytopbarRefsearch()
    {
        $this->resetPage();
    }
    public function updatedMytopbarRefDate()
    {
        $this->resetPage();
    }


    // scanLogProses
    public function scanLogProses()
    {
        //get table oracle local
        DB::table('tb_scanlog')->select('sn', 'scan_date', 'pin', 'verifymode', 'iomode', 'workcode')
            ->whereNotIn('scan_date', function ($q) {
                $q->select('at_date')->from('abtxn_attendancexts');
            })
            ->get()
            ->each(
                function ($item) {

                    // dd($item);
                    //cek record oracle RS // if exist update else insert
                    $cekrec = DB::table('abtxn_attendancexts')
                        ->where('at_date', $item->scan_date)
                        ->where('emp_id', $item->pin)
                        ->where('at_mode', $item->iomode)
                        ->first();

                    if ($cekrec) {
                        // update
                        DB::table('abtxn_attendancexts')
                            ->where('at_date', $item->scan_date)
                            ->where('emp_id', $item->pin)
                            ->where('at_mode', $item->iomode)
                            ->update([
                                'at_hour' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('H:i:s'),
                                'at_date' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('Y-m-d H:i:s'),
                                'at_mode' => $item->iomode,
                                'emp_id' => $item->pin,
                                'at_month' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('m'),
                                'at_year' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('Y'),
                            ]);
                    } else {
                        // insert
                        DB::table('abtxn_attendancexts')
                            ->insert([
                                'at_hour' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('H:i:s'),
                                'at_date' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('Y-m-d H:i:s'),
                                'at_mode' => $item->iomode,
                                'emp_id' => $item->pin,
                                'at_month' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('m'),
                                'at_year' => Carbon::createFromFormat('Y-m-d H:i:s', $item->scan_date)->format('Y'),
                            ]);
                    }
                }
            );
    }

    public function mount()
    {
        // Set TopBar
        $this->myTopBar['refDate'] = Carbon::now()->format('d/m/Y');
    }

    public function render()
    {
        // set mySearch
        $mySearch = $this->myTopBar['refSearch'];
        $myRefdate = $this->myTopBar['refDate'];
        // myQuery  /Collection
        $myQueryData = DB::table('abtxn_attendancexts')
            ->select(
                'at_hour',
                'at_date',
                'at_mode',
                'at_month',
                'at_year',
                'emp_id',
            )
            ->where(DB::raw('upper(emp_id)'), 'like', '%' . strtoupper($mySearch) . '%')
            ->where(DB::raw("to_char(to_date(at_date,'yyyy-mm-dd hh24:mi:ss'),'dd/mm/yyyy')"), '=', $myRefdate)
            ->where('at_mode', '=', '1')
            ->orderBy('emp_id', 'asc')
            ->paginate(100);
        // myQuery


        return view(
            'livewire.scan-log.scan-log-harian',
            ['myQueryData' => $myQueryData]
        );
    }
}
