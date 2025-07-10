<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Banner;
use Illuminate\Http\Request;
use function App\getSettings;
use App\Models\TransactionLog;
use App\Models\AdminSetting;
use App\Mail\BankStatementMail;

use Barryvdh\DomPDF\Facade\Pdf;
use function App\retErrorSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;

use App\Models\Beneficiary;

class MiscellaneousController extends Controller
{
    
    public function transactionDetails($tid) {
        $details = TransactionLog::where('transaction_id',$tid)->where('user_id', Auth::user()->id);
        if ($details->exists()) {
            $tranDetails = $details->first();
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'Transaction details retrieved successfully',
                    'data'=> $tranDetails
                ]
            ],200);
        }
        return response()->json([
            'status' => 'false',
            'data'=> [
                'message' => 'Transaction Not Found.',
            ]
        ],400);
    }
    
    public function getActiveBanners()
    {
        $activeBanners = Banner::where('status', 1)->get(['image']);
        
        $activeBanners->transform(function ($banner) {
            $banner->image = asset('admin' . $banner->image);
            return $banner;
        });

        return response()->json([
            'status' => 'success',
            'data' => $activeBanners
        ]);
    }
    
    public function broadcastMessage()
    {
        $messages = AdminSetting::where('name', 'broadcast')->get(['data', 'important']);

        $transformedMessages = $messages->map(function ($message) {
            return [
                'data' => $message->data,
                'status' => $message->important,
            ];
        });
    
        return response()->json([
            'message' => 'Status 0 is disabled while 1 is enabled', 
            'status' => 'success',
            'data' => $transformedMessages
        ]);
    }
    
    public function transferMessage()
    {
        $messages = AdminSetting::where('name', 'notification')->get(['data', 'important']);

        $transformedMessages = $messages->map(function ($message) {
            return [
                'data' => $message->data,
                'status' => $message->important,
            ];
        });
    
        return response()->json([
            'message' => 'Status 0 is disabled while 1 is enabled', 
            'status' => 'success',
            'data' => $transformedMessages
        ]);
    }
    
    public function downloadReceipt($format='image',$tid) {
        $details = TransactionLog::where('transaction_id',$tid)->where('user_id', Auth::user()->id);
        if ($details->doesntExist()) {
            return response()->json([
                'status' => 'false',
                'data'=> [
                    'message' => 'Transaction Not Found.',
                ]
            ],400);
        }
        ini_set('memory_limit','512M');
        set_time_limit(0);
        $tranDetails = $details->first();
        $type = strtolower($tranDetails->type);

        if($type == "transfer") {
            $view = "pdf.transfer";
        }elseif($type == "deposit") {
            $view = "pdf.deposit";
        }elseif($type == "airtime") {
            $view = "pdf.airtime";
        }elseif($type == "data") {
            $view = "pdf.data";
        }elseif($type == "electricity") {
            $view = "pdf.electricity";
        }elseif($type == "cable tv") {
            $view = "pdf.cable-tv";
        }elseif($type == "betting"){
            $view = "pdf.betting";
        }elseif($type == "gift card"){
            $view = "pdf.gift-card";
        }elseif($type == "jamb"){
            $view = "pdf.jamb";
        }elseif($type == "waec registration pin"){
            $view = "pdf.waec-registration-pin";
        }elseif($type == "top-up"){
            $view = "pdf.top-up";
        }elseif($type == "atc"){
            $view = "pdf.atc";
        }elseif($type == "sell gift card"){
            $view = "pdf.sell-gift-card";
        }elseif($type == "waec result checker pin"){
            $view = "pdf.waec-result-checker-pin";
        }

        $filename = 'Receipt'. $tranDetails->transaction_id;
        $pdf = Pdf::loadView($view, ['details'=>$tranDetails])->setPaper('a5', 'portrait');

        
        if ($format == "image") {
            $pdfPath = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($pdfPath, $pdf->output());
            
            // Convert PDF to Image using Imagick
            $imagick = new \Imagick($pdfPath);
            $imagick->setImageFormat('jpg'); // Change the format as needed
            
            // Save Image to a temporary file
            $imagePath = tempnam(sys_get_temp_dir(), 'image_');
            $imagick->writeImage($imagePath);

            // Output or download the image
            return response()->download($imagePath, $filename.'.jpg');
        }
        
        return $pdf->download($filename.'.pdf');

    }
    
    public function recentTransfer($page = 12) {
        $recent = TransactionLog::where("user_id", Auth::user()->id)->orderBy('created_at', 'desc')->where('type','transfer')->groupBy('recipient');
        if (isset($page) && is_numeric($page)) {
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent->paginate($page)
                ]
            ],200);
        }else {
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent->get()
                ]
            ],200);
        }
        // return response()->json([
        //     'status' => 'false',
        //     'data'=> [
        //         'message' => 'Failed to retrieved list',
        //     ]
        // ],400);
    }
    
    public function filterList() {
        $filt = TransactionLog::groupBy('status')->get(['status'])->toArray();
        return $filt;
    }

    public function allTransactions($page = null) {
        // !is_numeric($page) or empty($page) ? $page = 12 : false;
        $filters = $this->filterList();
        if (!empty($page) && is_numeric($page)) {
            $recent = TransactionLog::where("user_id", Auth::user()->id)->orderBy('created_at', 'desc')->paginate($page);
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent,
                    'filters' => $filters
                ]
            ],200);
        }elseif (!is_numeric($page)) {
            $recent = TransactionLog::where("user_id", Auth::user()->id)->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent,
                    'filters' => $filters
                ]
            ],200);
        }
        return response()->json([
            'status' => 'false',
            'data'=> [
                'message' => 'Failed to retrieved list',
            ]
        ],400);
    }
    
    public function allTransactionsTest($page = null) {
        // !is_numeric($page) or empty($page) ? $page = 12 : false;
        $filters = $this->filterList();
        if (!empty($page) && is_numeric($page)) {
            $recent = TransactionLog::where("user_id", Auth::user()->id)->orderBy('created_at', 'desc')->paginate($page);
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent,
                    'filters' => $filters
                ]
            ],200);
        }elseif (!is_numeric($page)) {
            $recent = TransactionLog::where("user_id", 359)->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $recent,
                    'filters' => $filters
                ]
            ],200);
        }
        return response()->json([
            'status' => 'false',
            'data'=> [
                'message' => 'Failed to retrieved list',
            ]
        ],400);
    }

    public function saveToken(Request $request)
    {
        $validatedData = Validator::make($request->all(['device_token']),[
            'device_token' => 'required'
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'status'=>'false',
                'data' => [
                    'message' => "Validation failed",
                    'error' => $validatedData->errors()
                ]
            ],400);
        }

        $request = Purify::clean($request->all());
        if (User::where('id',Auth::user()->id)->update(['device_token'=>$request['device_token']])) {
            return response()->json([
                'status'=> 'true',
                'data'=> [
                    'message'=> 'Notifications enabled successfully',
                ]
            ],200);
        }

        return response()->json([
            'status'=> 'false',
            'data'=> [
                'message'=> 'Unable to turn on notifications',
            ]
        ],400);
    }

    public function sendNotification(Request $request)
    {
        $request = Purify::clean($request->all());
        $firebaseToken = User::whereNotNull('device_token')->pluck('device_token')->all();

        $SERVER_API_KEY = getSettings('firebase','serverkey');
        if ($SERVER_API_KEY == "error") {return response()->json(retErrorSetting());}

        $data = [
            "registration_ids" => $firebaseToken,
            "notification" => [
                "title" => $request['title'],
                "body" => $request['body'],
                "icon" => asset('assets/images/bank/bank.jpg'),
                'click_action' => 'https://api.paypointapp.africa',
                "content_available" => true,
                "priority" => "high",
            ]
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        $response = curl_exec($ch);

        dd($response);
    }
    
    public function bankStatement(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'beginning' => 'required|date',
            'ending' => 'required|date',
        ], [
            'beginning.required' => 'Start date is required',
            'ending.required' => 'End date is required',
            'beginning.date' => 'Start date must be a valid date',
            'ending.date' => 'End date must be a valid date',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ]
            ], 422);
        }
    
        try {
            $clean = Purify::clean($request->only(['beginning', 'ending']));
            $startDate = $clean['beginning'];
            $endDate = $clean['ending'];
            $user = Auth::user();
            $userEmail = $user->email;
    
            $transactions = TransactionLog::where('user_id', $user->id)
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->orderBy('created_at', 'desc')
                ->get();
    
            $pdf = Pdf::loadView('pdf.bank_statement', [
                'transactions' => $transactions,
                'start' => $startDate,
                'end' => $endDate
            ])->setPaper('a4', 'landscape');
    
            $filename = "{$user->first_name}_{$user->surname}_bank_statement_{$startDate}_{$endDate}.pdf";
    
            $pdfContent = $pdf->output();
    
            Mail::to($userEmail)->send(new BankStatementMail($pdfContent, $filename, $startDate, $endDate));
    
            return response()->json([
                'status' => true,
                'data' => [
                    'message' => "Bank statement successfully emailed to {$userEmail}"
                ]
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'data' => [
                    'message' => 'Failed to generate statement. Please try again.',
                    'error' => $e->getMessage()
                ]
            ], 500);
        }
    }
    
    public function notificationsList($page = null) {
        $nots = Auth::user()->notifications();
        if (isset($page) && is_numeric($page)) {
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $nots->paginate($page)
                ]
            ],200);
        } else {
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'List retrieved successfully',
                    'data'=> $nots->get()
                ]
            ],200);
        }
    }
    
    public function getBeneficiariesByType(Request $request, $type)
    {
        
        $allowedTypes = ['betting', 'transfer', 'electricity', 'cable_tv', 'data', 'airtime'];
        if (!in_array($type, $allowedTypes)) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => 'Invalid type specified. Allowed types are: betting, transfer, electricity, cable_tv.'
                ]
            ], 400);
        }
    
        // Retrieve beneficiaries for the authenticated user filtered by type
        $beneficiaries = Beneficiary::where('user_id', Auth::id())
            ->where('type', $type)
            ->get();
    
        return response()->json([
            'status' => 'true',
            'data' => [
                'beneficiaries' => $beneficiaries
            ]
        ], 200);
    }
    
    public function destroy($id)
    {
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beneficiary->delete();
    
            return response()->json([
                'status' => 'true',
                'data'=> [
                    'message' => 'Beneficiary deleted successfully.'
                ]
                
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => 'Beneficiary not found.'
                ]
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'false',
                'data' => [
                    'message' => 'An error occurred while deleting the beneficiary: ' . $e->getMessage()
                ]
                
            ], 500);
        }
}


}


