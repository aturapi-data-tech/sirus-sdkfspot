<?php

namespace App\Livewire\ScanLog;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Livewire\WithPagination;
use Carbon\Carbon;

use Livewire\Component;


class ScanLogHarianInOut extends Component
{
    use WithPagination;

    // primitive Variable
    public string $myTitle = 'Absensi';
    public string $mySnipet = 'Data Absensi Hari ini ';

    // TopBar
    public array $myTopBar = [
        'refDate' => '',
        'refSearch' => '',
        'refShift' => ''
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

    //////////////////////////////////////////
    /////////////////////////////////////////
    ///////////////////////////////////////
    private static function sendError($error, $errorMessages = [], $code = 404, $url, $requestTransferTime)
    {
        $response = [
            'metadata' => [
                'message' => $error,
                'code' => $code,
            ],
        ];
        if (!empty($errorMessages)) {
            $response['response'] = $errorMessages;
        }
        // Insert webLogStatus
        DB::table('web_log_status')->insert([
            'code' =>  $code,
            'date_ref' => Carbon::now(),
            'response' => json_encode($response, true),
            'http_req' => $url,
            'requestTransferTime' => $requestTransferTime
        ]);

        return $response;
    }

    private static function sendResponse($message, $data, $code = 200, $url, $requestTransferTime)
    {
        $response = [
            'response' => $data,
            'metadata' => [
                'message' => $message,
                'code' => $code,
            ],
        ];

        // Insert webLogStatus
        DB::table('web_log_status')->insert([
            'code' =>  $code,
            'date_ref' => Carbon::now(),
            'response' => json_encode($response, true),
            'http_req' => $url,
            'requestTransferTime' => $requestTransferTime
        ]);

        return $response;
    }
    //////////////////////////////////////////
    /////////////////////////////////////////
    ///////////////////////////////////////

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

    // scanLogProses
    public function scanLogProses()
    {

        // 1. Get data from machine
        $DataScanLog = $this->getDataScanLogtoMachine();

        // 2. Insert data tp tb_scanlog
        if (isset($DataScanLog['response'])) {
            foreach ($DataScanLog['response'] as $item) {
                DB::table('tb_scanlog')
                    ->insert([
                        'sn' => $item['SN'],
                        'scan_date' => $item['ScanDate'],
                        'pin' => trim($item['PIN'], ' '),
                        'verifymode' => $item['VerifyMode'],
                        'iomode' =>  $item['IOMode'],
                        'workcode' =>  $item['WorkCode'],
                    ]);
            }
        }

        // 3. Loop tb_scanlog memindahkan data ke -> (abtxn_attendancexts) 
        // --XXX celah ada pada scan_date bisa jadi ketika alat lebih dari 1 ada scan_date yang sama 
        // dan data tidak terdeteksi   
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

        // 4. hapus data tb_scanlog
        DB::table('tb_scanlog')->delete();

        // 5. hapus data mesin
        // $this->delDataScanLogtoMachine();
    }

    public function scanLogProsesNew()
    {

        // 1. Get data from machine
        $DataScanLog = $this->getDataScanLogtoMachineNew();

        // 2. Insert data tp tb_scanlog
        if (isset($DataScanLog['response'])) {
            foreach ($DataScanLog['response'] as $item) {
                DB::table('tb_scanlog')
                    ->insert([
                        'sn' => $item['SN'],
                        'scan_date' => $item['ScanDate'],
                        'pin' => trim($item['PIN'], ' '),
                        'verifymode' => $item['VerifyMode'],
                        'iomode' =>  $item['IOMode'],
                        'workcode' =>  $item['WorkCode'],
                    ]);
            }
        }

        // 3. Loop tb_scanlog memindahkan data ke -> (abtxn_attendancexts) 
        // --XXX celah ada pada scan_date bisa jadi ketika alat lebih dari 1 ada scan_date yang sama 
        // dan data tidak terdeteksi   
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

        // 4. hapus data tb_scanlog
        DB::table('tb_scanlog')->delete();

        // 5. hapus data mesin
        // $this->delDataScanLogtoMachine();
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

    private function getDataScanLogtoMachine()
    {
        $r = ["sn" =>  env('FSERVICE_SN')];
        $rules = ["sn" => "required"];
        $validator = Validator::make($r, $rules);

        if ($validator->fails()) {
            // error, msgError,Code,url,ReqtrfTime
            return self::sendError($validator->errors()->first(), $validator->errors(), 201, null, null);
        }


        // handler when time out and off line mode
        try {

            $url = env('FSERVICE') . "/scanlog/all/paging";
            $response = Http::asForm()
                ->timeout(30)
                ->post(
                    $url,
                    ["sn" => $r['sn']]
                );


            // dd($response->getBody()->getContents());
            // decode Response dari Json ke array
            $myResponse = json_decode($response->getBody()->getContents(), true);

            if (isset($myResponse['Data'])) {
                return self::sendResponse('success', $myResponse['Data'], 200, $url, $response->transferStats->getTransferTime());
            } else {

                $devInfo = [];
                return self::sendError('FingerSpot Tidak Merespons', $devInfo, 408, $url, null);
            }
            /////////////////////////////////////////////////////////////////////////////
        } catch (Exception $e) {
            // error, msgError,Code,url,ReqtrfTime

            return self::sendError($e->getMessage(), $validator->errors(), 408, $url, null);
        }
    }

    private function getDataScanLogtoMachineNew()
    {
        $r = ["sn" =>  env('FSERVICE_SN')];
        $rules = ["sn" => "required"];
        $validator = Validator::make($r, $rules);

        if ($validator->fails()) {
            // error, msgError,Code,url,ReqtrfTime
            return self::sendError($validator->errors()->first(), $validator->errors(), 201, null, null);
        }


        // handler when time out and off line mode
        try {

            $url = env('FSERVICE') . "/scanlog/new";
            $response = Http::asForm()
                ->timeout(30)
                ->post(
                    $url,
                    ["sn" => $r['sn']]
                );


            // dd($response->getBody()->getContents());
            // decode Response dari Json ke array
            $myResponse = json_decode($response->getBody()->getContents(), true);

            if (isset($myResponse['Data'])) {
                return self::sendResponse('success', $myResponse['Data'], 200, $url, $response->transferStats->getTransferTime());
            } else {

                $devInfo = [];
                return self::sendError('FingerSpot Tidak Merespons', $devInfo, 408, $url, null);
            }
            /////////////////////////////////////////////////////////////////////////////
        } catch (Exception $e) {
            // error, msgError,Code,url,ReqtrfTime

            return self::sendError($e->getMessage(), $validator->errors(), 408, $url, null);
        }
    }

    public function delDataScanLogtoMachine()
    {
        $r = ["sn" =>  env('FSERVICE_SN')];
        $rules = ["sn" => "required"];
        $validator = Validator::make($r, $rules);

        if ($validator->fails()) {
            // error, msgError,Code,url,ReqtrfTime
            return self::sendError($validator->errors()->first(), $validator->errors(), 201, null, null);
        }


        // handler when time out and off line mode
        try {

            $url = env('FSERVICE') . "/scanlog/del";
            $response = Http::asForm()
                ->timeout(30)
                ->post(
                    $url,
                    ["sn" => $r['sn']]
                );


            // dd($response->getBody()->getContents());
            // decode Response dari Json ke array
            $myResponse = json_decode($response->getBody()->getContents(), true);

            if (isset($myResponse['Data'])) {
                return self::sendResponse('success', $myResponse['Data'], 200, $url, $response->transferStats->getTransferTime());
            } else {

                $devInfo = [];
                return self::sendError('FingerSpot Tidak Merespons', $devInfo, 408, $url, null);
            }
            /////////////////////////////////////////////////////////////////////////////
        } catch (Exception $e) {
            // error, msgError,Code,url,ReqtrfTime

            return self::sendError($e->getMessage(), $validator->errors(), 408, $url, null);
        }
    }

    public function getDevInfoMachine()
    {
        $r = ["sn" =>  env('FSERVICE_SN')];
        $rules = ["sn" => "required"];
        $validator = Validator::make($r, $rules);

        if ($validator->fails()) {
            // error, msgError,Code,url,ReqtrfTime
            return self::sendError($validator->errors()->first(), $validator->errors(), 201, null, null);
        }


        // handler when time out and off line mode
        try {

            $url = env('FSERVICE') . "/dev/info";
            $response = Http::asForm()
                ->timeout(30)
                ->post(
                    $url,
                    ["sn" => $r['sn']]
                );

            // decode Response dari Json ke array
            $myResponse = json_decode($response->getBody()->getContents(), true);
            dd($myResponse);

            if (isset($myResponse['DEVINFO'])) {
                return self::sendResponse('success', $myResponse, 200, $url, $response->transferStats->getTransferTime());
            } else {

                $devInfo = [];
                return self::sendError('FingerSpot Tidak Merespons', $devInfo, 408, $url, null);
            }
            /////////////////////////////////////////////////////////////////////////////
        } catch (Exception $e) {
            // error, msgError,Code,url,ReqtrfTime

            return self::sendError($e->getMessage(), $validator->errors(), 408, $url, null);
        }
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
        $myQueryData = DB::table('abview_cekincekouts')
            ->select(
                'at_hour_i',
                'at_hour_o',
                'at_date_i',
                DB::raw('ROUND(selisih_durasi_save_time * (24 * 60),0) as selisih_durasi_save_time'),
                DB::raw('ROUND(selisih_durasi_terlambat * (24 * 60),0) as selisih_durasi_terlambat'),
                DB::raw('ROUND(selisih_pulang_save_time * (24 * 60),0) as selisih_pulang_save_time'),
                DB::raw('ROUND(selisih_pulang_terlambat * (24 * 60),0) as selisih_pulang_terlambat'),
                'emp_id',
                'emp_name',

            )
            ->where(DB::raw("to_char(at_date_i,'dd/mm/yyyy')"), '=', $myRefdate)
            ->where("shift_id", '=', $myRefshift);

        $myQueryData->where(function ($q) use ($mySearch) {
            $q->orWhere(DB::raw('upper(emp_id)'), 'like', '%' . strtoupper($mySearch) . '%')
                ->orWhere(DB::raw('upper(emp_name)'), 'like', '%' . strtoupper($mySearch) . '%');
        })

            ->orderBy('emp_id', 'asc');
        // myQuery


        return view(
            'livewire.scan-log.scan-log-harian-in-out',
            ['myQueryData' => $myQueryData->paginate(20)]
        );
    }
}
