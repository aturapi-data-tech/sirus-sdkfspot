<?php

namespace App\Livewire\ScanLog;

use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
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
        // DB::table('tb_scanlog')->delete();

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

    private function delDataScanLogtoMachine()
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
            ['myQueryData' => $myQueryData->paginate(20)]
        );
    }
}
