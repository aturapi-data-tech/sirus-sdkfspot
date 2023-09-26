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

    // scanLogProses
    public function userProses()
    {
        //get table oracle local
        DB::table('tb_user')->select('pin')
            ->whereNotIn('pin', function ($q) {
                $q->select('emp_id')->from('abmst_employers');
            })
            ->get()
            ->each(
                function ($item) {

                    // dd($item);
                    //cek record oracle RS // if exist update else insert
                    $cekrec = DB::table('abmst_employers')
                        ->where('emp_id', $item->pin)
                        ->first();

                    if ($cekrec) {
                        // update
                        DB::table('abmst_employers')
                            ->where('emp_id', $item->pin)
                            ->update([
                                'emp_id' => $item->pin,
                                'po_id' => '13',
                            ]);
                    } else {
                        // insert
                        DB::table('abmst_employers')
                            ->insert([
                                'emp_id' => $item->pin,
                                'po_id' => '13',
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
        $myQueryData = DB::table('abview_cekins')
            ->select(
                'at_hour_i',
                'at_date_i',
                'at_mode',
                'emp_id',
                'emp_name',
                'emp_jabatan',
                'emp_keterangan',
            )
            ->where(DB::raw("to_char(at_date_i,'dd/mm/yyyy')"), '=', $myRefdate);

        $myQueryData->where(function ($q) use ($mySearch) {
            $q->orWhere(DB::raw('upper(emp_id)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_name)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_jabatan)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_keterangan)'), 'like', '%' . strtoupper($mySearch) . '%');
        })

            ->orderBy('emp_id', 'asc');
        // myQuery


        return view(
            'livewire.scan-log.scan-log-harian',
            ['myQueryData' => $myQueryData->paginate(100)]
        );
    }
}
