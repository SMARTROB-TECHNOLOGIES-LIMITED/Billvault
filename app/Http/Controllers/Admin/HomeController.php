<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AdminSetting;
use App\Models\TransactionLog;
use App\Models\Banner;
use App\Models\LoginActivity;
use App\Models\KycLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Ramsey\Uuid\Uuid;
use App\Jobs\TransactionLog as ModelLog;


class HomeController extends Controller
{
    public function dashboard () {
        $pg = "Dashboard";
        $day = date('d');
        $custs = User::count();
        $tot_trf = TransactionLog::where('type','transfer')->where('status','!=','reversed')->sum('amount');
        $tot_dpst = TransactionLog::where('type','deposit')->sum('amount');
        $tot_vtu = TransactionLog::where('status','!=','reversed')->where(function($query){
                        return $query->where('type','electricity')->orWhere('type','data')->orWhere('type','airtime')->orWhere('type','cable tv');
                    })->sum('amount');

        $tdy_trf = TransactionLog::select(DB::raw('SUM(CASE WHEN DAY(created_at) = '.$day.' THEN amount ELSE 0 END) as amount'))->where('type','transfer')->where('status','!=','reversed')->first()->amount;
        $tdy_dpst = TransactionLog::select(DB::raw('SUM(CASE WHEN DAY(created_at) = '.$day.' THEN amount ELSE 0 END) as amount'))->where('type','deposit')->sum('amount');
        $ref = DB::table('users as f')
                ->select('f.*', DB::raw('COUNT(u.id) as total_ref'))
                ->leftJoin('users as u', 'f.username', '=', 'u.referral')
                ->where('f.complete',1)
                ->groupBy('id', 'email', 'username')
                ->orderBy('total_ref','desc')
                ->take('20')
                ->get();
        $recentTransactions = TransactionLog::with('user')->latest()->get();
        
        

        $analysis = TransactionLog::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month_num'),
            DB::raw('MONTHNAME(created_at) as month'),
            DB::raw('SUM(CASE WHEN type = "transfer" THEN amount ELSE 0 END) as transfer'),
            DB::raw('SUM(CASE WHEN type = "deposit" THEN amount ELSE 0 END) as deposit')
        )->where('status','!=','reversed')->groupBy('month_num', 'year')->orderBy('year', 'asc')->orderBy('month_num', 'asc')->take(12)->get();
        
//         foreach ($recentTransactions as $transaction) {
//     // Access the user data for each transaction log
//     $user = $transaction->user->first_name;
//     // Do something with the user data...
//     dd($user);
// }
        
        $vtanalysis = TransactionLog::select('type', DB::raw('SUM(amount) as amount'))->where('type','!=','transfer')->where('type','!=','deposit')->groupBy('type')->get();
        // dd($vtanalysis);
        return view("admin.dashboard", compact(['pg','custs','analysis','vtanalysis','ref','tot_trf','tot_dpst','tdy_trf','tdy_dpst','tot_vtu','recentTransactions']));
    }
    
    public function loginHistory () {
        
        $activities = LoginActivity::with('user')->orderBy('logged_in_at', 'desc')->get();
        
        // dd($activities);

        return view('admin.login-history', compact(['activities']));
    }

    public function customerList () {
        $pg = 'Customers List';

        $list = DB::table('users as f')
                ->select('f.*', DB::raw('COUNT(u.id) as total_ref'))
                ->leftJoin('users as u', 'f.username', '=', 'u.referral')
                ->groupBy('id', 'email', 'username')->latest()
                ->get();
   
        return view('admin.users.list', compact(['pg','list']));
    }
    
    public function customerTopup () {
        $pg = 'User Top up';

        $list = DB::table('users as f')
            ->select('f.*')
            ->where('f.complete', 1) // Filter users where complete = 1
            ->latest()
            ->get();

        return view('admin.users.topup', compact(['pg','list']));
    }
    
    public function topUpUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
        ]);
    
        $user = User::findOrFail($request->user_id);
        $amount = $request->amount;
        $purpose = $request->purpose ?? 'Top Up';
        
        $balanceBefore = $user->balance;
        $balanceAfter= $balanceBefore+$amount;
    
        $user->balance += $amount;
        $user->save();
        
        $ref = Str::random(20);
        
        $data = json_encode([
            'id' => $ref,
            'img' => asset('assets/images/bank/bank.jpg'),
            'status' => "Successful",
            'reference' => $ref,
            'amount' => (float) ($amount),
            'account_number' => $user->account_number,
            'bank'  =>  "Bill Vault",
            'purpose' => $purpose,
            'balance_before'    => $balanceBefore,
            'balance_after'     => $balanceAfter,
        ]);
    
        // Log the transaction
        ModelLog::dispatch($user->id, "Top-up", $amount, $ref, "Success", $user->email, $data);
    
        Session::flash('alert',['t'=>'Success','m'=>'Top-up successful!']); return redirect()->back();
        
    }


    public function transactionSet() {
        $pg = 'Transaction Settings/Rates';
        $qtf = AdminSetting::where('name','transfer')->first();
        $qdp = AdminSetting::where('name','deposit')->first();
        $cardData = AdminSetting::where('name','card_charges')->first();
        $exchangeRateData = AdminSetting::where('name','exchange_rate')->first();
        
        $transfer = json_decode($qtf->data ?? '', true) ?? collect([]);
        $deposit = json_decode($qdp->data ?? '', true) ?? collect([]);
        $card = json_decode($cardData->data ?? '', true) ?? collect([]);
        $exchangeRate = json_decode($exchangeRateData->data ?? '', true) ?? collect([]);

        return view('admin.settings.transaction', compact(['pg','transfer','deposit', 'card', 'exchangeRate']));
    }

    public function utilitySet() {
        $pg = 'Utility Bills Settings/Fees';
        $qel = AdminSetting::where('name','electricity')->first(['data'])->data ?? 0;
        $qct = AdminSetting::where('name','cable')->first(['data'])->data ?? 0;
        $qwc = AdminSetting::where('name','waec')->first(['data'])->data ?? 0;
        $qjb = AdminSetting::where('name','jamb')->first(['data'])->data ?? 0;
        return view('admin.settings.utility', compact(['pg','qel','qct','qwc','qjb']));
    }

    public function utilitySetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'electricity' => ['required','numeric'],
            'cable' => ['required','numeric'],
            'waec' => ['required','numeric'],
            'jamb' => ['required','numeric'],
        ],[
            'smtpport.required' => "SMTP port is required",
            'smtpport.numeric' => "Field accepts numeric characters only",
            'electricity.required' => 'Field is required',
            'electricity.numeric' => 'Field must be numeric',
            'cable.required' => 'Field is required',
            'cable.numeric' => 'Field must be numeric',
            'waec.required' => 'Field is required',
            'waec.numeric' => 'Field must be numeric',
            'jamb.required' => 'Field is required',
            'jamb.numeric' => 'Field must be numeric',
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));

        $qelect = AdminSetting::where('name', 'electricity');
        if ($qelect->exists()) {$relect = AdminSetting::where('name', 'electricity')->update(['data'=>$request['electricity']]);
        }else {$relect = AdminSetting::create(['name'=>'electricity','data'=>$request['electricity']]);}

        $qcable = AdminSetting::where('name', 'cable');
        if ($qcable->exists()) {$rcable = AdminSetting::where('name', 'cable')->update(['data'=>$request['cable']]);
        }else {$rcable = AdminSetting::create(['name'=>'cable','data'=>$request['cable']]);}

        $qwaec = AdminSetting::where('name', 'waec');
        if ($qwaec->exists()) {$rwaec = AdminSetting::where('name', 'waec')->update(['data'=>$request['waec']]);
        }else {$rwaec = AdminSetting::create(['name'=>'waec','data'=>$request['waec']]);}

        $qjamb = AdminSetting::where('name', 'jamb');
        if ($qjamb->exists()) {$rjamb = AdminSetting::where('name', 'jamb')->update(['data'=>$request['jamb']]);
        }else {$rjamb = AdminSetting::create(['name'=>'jamb','data'=>$request['jamb']]);}



        if ($relect && $rcable && $rwaec && $rjamb) {
            Session::flash('alert',['t'=>'Success','m'=>'Utility settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }
    
    public function bannerSet() {
        $pg = 'Banner Settings';
        $banners = Banner::all();
       
        return view('admin.settings.banner', compact(['pg', 'banners']));
    }
    
    public function uploadBanners(Request $request) {
        $images = $request->file('images');
        // dd($images);
        foreach ($images as $image) {
            $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $raw = $image->storeAs('public/banners', $filename);
            $path = storage_path('app/'.$raw); 
            $new_path = '/storage'.str_replace(storage_path(), '', $path);
            $file_name = str_replace("\\", "/", $new_path);
            Banner::create(['image' => $file_name, 'status' => 1]);
        }
        
        Session::flash('alert',['t'=>'Success','m'=>'Images uploaded successfully.']); return redirect()->back();
    
    }
    
    public function deleteBanner(Banner $banner){
        $banner->delete();
        Session::flash('alert',['t'=>'Success','m'=>'Banner deleted successfully.']); return redirect()->back();
    }
    
    public function toogleBanner (Banner $banner, $bid) {
        $p = $banner->where('id',$bid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Image not found']); return redirect()->back();}

        $d = $p->first();
        $parr = [];
        $parr = $d->status == 1 ? [0,'deactivated'] : [1,'activated'];
        if ($d->update(['status'=>$parr[0]])) {
            Session::flash('alert',['t'=>'Success','m'=>'Image '.$parr[1].' successfully.']); return redirect()->back();
        }
        Session::flash('alert',[
            't'=>'Error',
            'm'=>'Something went wrong, Try again.'
        ]); 
        return redirect()->back();

    }

    public function toogleUser (User $user, $uid) {
        $p = $user->where('id',$uid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Account not found']); return redirect()->back();}

        $d = $p->first();
        $parr = [];
        $parr = $d->is_ban == 1 ? [0,'activated'] : [1,'banned'];
        if ($d->update(['is_ban'=>$parr[0]])) {
            Session::flash('alert',['t'=>'Success','m'=>'Account '.$parr[1].' successfully.']); return redirect()->back();
        }
        Session::flash('alert',[
            't'=>'Error',
            'm'=>'Something went wrong, Try again.'
        ]); 
        return redirect()->back();

    }
    
    public function toogleUserRestriction (User $user, $uid) {
        $p = $user->where('id',$uid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Account not found']); return redirect()->back();}

        $d = $p->first();
        $parr = [];
        $parr = $d->is_account_restricted == 1 ? [0,'Activated'] : [1,'Restricted'];
        if ($d->update(['is_account_restricted'=>$parr[0]])) {
            Session::flash('alert',['t'=>'Success','m'=>'Account '.$parr[1].' successfully.']); return redirect()->back();
        }
        Session::flash('alert',[
            't'=>'Error',
            'm'=>'Something went wrong, Try again.'
        ]); 
        return redirect()->back();

    }
    
    public function toogleUserLogin (User $user, $uid) {
        $p = $user->where('id',$uid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Account not found']); return redirect()->back();}

        $d = $p->first();
        $parr = [];
        $parr = $d->view == 1 ? [0,'Disabled'] : [1,'Enabled'];
        if ($d->update(['view'=>$parr[0]])) {
            Session::flash('alert',['t'=>'Success','m'=>'Account Login '.$parr[1].' successfully.']); return redirect()->back();
        }
        Session::flash('alert',[
            't'=>'Error',
            'm'=>'Something went wrong, Try again.'
        ]); 
        return redirect()->back();

    }
    
    public function update(Request $request, $id){
        $person = User::findOrFail($id);

        // Update the fields based on the form data
        $person->update($request->all());
        
        Session::flash('alert',['t'=>'Success','m'=>'Account updated successfully.']); return redirect()->back();
        
    }
    
    public function deleteUser (User $user, $uid){
        $p = $user->where('id',$uid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Account not found']); return redirect()->back();}
        $d = $p->first();
        
        if ($d->delete()) {
           Session::flash('alert',['t'=>'Success','m'=>'Account deleted successfully.']); return redirect()->back();
        } 
        Session::flash('alert',[
            't'=>'Error',
            'm'=>'Something went wrong, Try again.'
        ]); 
        return redirect()->back();
    }

    public function viewDetails (User $user, TransactionLog $trans, $uid) {
        $p = $user->where('id',$uid);
        if ($p->doesntExist()) {Session::flash('alert',['t'=>'Error','m'=>'Account not found']); return redirect()->back();}
        $person = $p->first();

        $referrals = $user->where('referral','!=','')->where('referral', $person->username)->get(['id','first_name','surname','username']);
        $transactions = $trans->where('user_id', $person->id)->get();
        
        $activities = LoginActivity::where('user_id', $uid)->get();

        return view('admin.users.details', compact(['person','transactions','referrals', 'activities']));
    }

    public function transferSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'tfInt.*' => ['required','numeric'],
            'tfFee.*' => ['required','numeric'],
        ],[
            'tfInt.*.required' => 'Field is required',
            'tfInt.*.numeric' => 'Field must be a number',
            'tfFee.*.required' => 'Field is required',
            'tfFee.*.numeric' => 'Field must be a number',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'reason' => 'validation',
                'message' => 'Validation failed. Check fields and try again',
                'error' => $validatedData->errors()
            ],400);
        }

        $request = Purify::clean($request->except('_token'));
        $data = [];
        foreach ($request['tfInt'] as $i => $key) {
            $data[] = ['range' => $key, "fee" => $request['tfFee'][$i]];
        }

        $q = AdminSetting::where('name', 'transfer');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'transfer')->update(['data'=>json_encode($data)]);
        }else {
            $r = AdminSetting::create(['name'=>'transfer','data'=>json_encode($data)]);
        }
        if ($r) {
            return response()->json(['t'=>'Success','m'=>'Settings updated successfully.'],200);
        }
        return response()->json(['reason'=>'alert','t'=>'Error','m'=>'Something went wrong, Try again.'],400);
    }

    public function depositSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'dpInt.*' => ['required','numeric'],
            'dpFee.*' => ['required','numeric','max:100'],
            'dpAddOn.*' => ['required','numeric'],
        ],[
            'dpInt.*.required' => 'Field is required',
            'dpInt.*.numeric' => 'Field must be a number',
            'dpFee.*.required' => 'Field is required',
            'dpFee.*.numeric' => 'Field must be a number',
            'dpFee.*.max' => 'Between 1-100%',
            'dpAddOn.*.required' => 'Field is required',
            'dpAddOn.*.numeric' => 'Field must be a number',
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'reason' => 'validation',
                'message' => 'Validation failed. Check fields and try again',
                'error' => $validatedData->errors()
            ],400);
        }

        $request = Purify::clean($request->except('_token'));
        $data = [];
        foreach ($request['dpInt'] as $i => $key) {
            $data[] = ['range' => $key, "fee" => (float)($request['dpFee'][$i] / 100), 'addOn' => (float)$request['dpAddOn'][$i]];
        }

        $q = AdminSetting::where('name', 'deposit');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'deposit')->update(['data'=>json_encode($data)]);
        }else {
            $r = AdminSetting::create(['name'=>'deposit','data'=>json_encode($data)]);
        }
        if ($r) {
            return response()->json(['t'=>'Success','m'=>'Settings updated successfully.'],200);
        }
        return response()->json(['reason'=>'alert','t'=>'Error','m'=>'Something went wrong, Try again.'],400);
    }
    
    public function cardSetSubmit(Request $request){
        $validatedData = Validator::make($request->all(), [
            'card_charges' => ['required','numeric'],
            'card_addon' => ['required','numeric'],
            'top_up' => ['required','numeric'],
            'deposit' => ['required','numeric', 'min:5']
        ],[
            'card_charges.required' => 'Field is required',
            'card_charges.numeric' => 'Field must be a number',
            'card_addon.required' => 'Field is required',
            'card_addon.numeric' => 'Field must be a number',
            'deposit.required' => 'Deposit is required',
            'deposit.numeric' => 'Deposit must be a number',
            'deposit.max' => 'Minimum of 5 USD',
            'top_up.required' => 'Field is required',
            'top_up.numeric' => 'Field must be a number',
            
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'reason' => 'validation',
                'message' => 'Validation failed. Check fields and try again',
                'error' => $validatedData->errors()
            ],400);
        }

        $data = Purify::clean($request->except('_token'));
        
        $r = AdminSetting::where('name', 'card_charges')->update(['data'=>json_encode($data)]);
        Session::flash('alert',['t'=>'Success','m'=>'Settings updated successfully.']); return redirect()->back();
        // return response()->json(['t'=>'Success','m'=>'Settings updated successfully.'],200);
         
    }
    
    public function exchangeRateSubmit(Request $request){
        $validatedData = Validator::make($request->all(), [
            'ngn_usd' => ['required','numeric'],
            'usd_ngn' => ['required','numeric'],
        ],[
            'ngn_usd.required' => 'Naira to Dollar field is required',
            'ngn_usd.numeric' => 'Field must be a number',
            'usd_ngn.required' => 'Dollar to Naira field is required',
            'usd_ngn.numeric' => 'Field must be a number',
            
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                'reason' => 'validation',
                'message' => 'Validation failed. Check fields and try again',
                'error' => $validatedData->errors()
            ],400);
        }

        $data = Purify::clean($request->except('_token'));
        
        $r = AdminSetting::where('name', 'exchange_rate')->update(['data'=>json_encode($data)]);
        Session::flash('alert',['t'=>'Success','m'=>'Settings updated successfully.']); return redirect()->back();
        // return response()->json(['t'=>'Success','m'=>'Settings updated successfully.'],200);
         
    }

    public function smtpSet() {
        $q = AdminSetting::where('name', 'smtp')->first();
        $set = json_decode($q->data ?? '') ?? collect([]);
        return view('admin.settings.smtp', compact(['set']));
    }
    
    public function smtpSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'smtphost' => ['required','string'],
            'smtpport' => ['required','numeric'],
            'smtpfrom' => ['required','email'],
            'smtpusername' => ['required','string'],
            'smtppassword' => ['required','string'],
        ],[
            'smtphost.required' => "SMTP host url is required",
            'smtphost.url' => "Accepts valid input only",
            'smtpport.required' => "SMTP port is required",
            'smtpport.numeric' => "Field accepts numeric characters only",
            'smtpfrom.required' => 'The "from" email address is required',
            'smtpfrom.email' => "Field must be a valid email address",
            'smtpusername.required' => "SMTP username is required for authentication",
            'smtpusername.string' => "Accepts valid inputs only",
            'smtppassword.required' => "SMTP password is required for authentication",
            'smtppassword.string' => "Accepts valid inputs only",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'smtp');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'smtp')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'smtp','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }

    public function thirdPartySet() {
        $pq = AdminSetting::where('name', 'paystack')->first();
        $set = json_decode($pq->data ?? '') ?? collect([]);

        $vq = AdminSetting::where('name', 'vtpass')->first();
        $vtp = json_decode($vq->data ?? '') ?? collect([]);

        $yq = AdminSetting::where('name', 'youverify')->first();
        $you = json_decode($yq->data ?? '') ?? collect([]);

        $fq = AdminSetting::where('name', 'firebase')->first();
        $fcm = json_decode($fq->data ?? '') ?? collect([]);
        return view('admin.settings.third-party', compact(['set','vtp','you','fcm']));
    }
    
    public function referralSettings() {
        
        $fq = AdminSetting::where('name', 'referral')->first();
        $fcm = json_decode($fq->data ?? '') ?? collect([]);
        return view('admin.settings.referral', compact(['fcm']));
    }
    
    public function broadCastSet() {
        $brd = AdminSetting::where('name', 'broadcast')->first();

        $not = AdminSetting::where('name', 'notification')->first();
        
        return view('admin.settings.broadcast-message', compact(['brd','not']));
    }
    
     public function referralBonus(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'bonus' => ['required'],
            'limit' => ['required'],
        ],[
            'bonus.required' => "Bonus value is need",
            'limit.required' => "Limit value is required",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'referral');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'referral')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'referral','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Referral settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }
    
    public function paystackSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'publickeypaystack' => ['required','string'],
            'secretkeypaystack' => ['required','string'],
        ],[
            'publickeypaystack.required' => "Paystack public key is required for authentication",
            'publickeypaystack.string' => "Field accepts valid inputs only",
            'secretkeypaystack.required' => "Paystack secret key is required for authentication",
            'secretkeypaystack.string' => "Field accepts valid inputs only",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'paystack');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'paystack')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'paystack','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Paystack settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }

    public function vtpassSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'apikeyvtpass' => ['required','string'],
            'secretkeyvtpass' => ['required','string'],
        ],[
            'apikeyvtpass.required' => "VTPass api key is required for authentication",
            'apikeyvtpass.string' => "Field accepts valid inputs only",
            'secretkeyvtpass.required' => "VTPass secret key is required for authentication",
            'secretkeyvtpass.string' => "Field accepts valid inputs only",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'vtpass');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'vtpass')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'vtpass','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'VTPass settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }

    public function youverifySetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'apitoken' => ['required','string'],
        ],[
            'apitoken.required' => "Youverify Api token is required for authentication",
            'apitoken.string' => "Field accepts valid inputs only",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'youverify');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'youverify')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'youverify','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Youverify settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }

    public function firebaseSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'serverkey' => ['required','string'],
        ],[
            'serverkey.required' => "Firebase server key is required for authentication",
            'serverkey.string' => "Field accepts valid inputs only",
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'firebase');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'firebase')->update(['data'=>json_encode($request)]);
        }else {
            $r = AdminSetting::create(['name'=>'firebase','data'=>json_encode($request)]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Firebase settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }
    
    public function broadcastSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'message' => ['required','string'],
            'status' => ['required','string'],
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        // $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'broadcast');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'broadcast')->update(['data'=>$request->message, 'important' => $request->status]);
        }else {
            $r = AdminSetting::create(['name'=>'broadcast','data'=>$request->message, 'important' => $request->status]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Broadcast message settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }
    
    public function notificationSetSubmit(Request $request) {
        $validatedData = Validator::make($request->all(), [
            'message' => ['required','string'],
            'status' => ['required','string'],
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData->errors());
        }

        // $request = Purify::clean($request->except('_token'));
        $q = AdminSetting::where('name', 'notification');
        if ($q->exists()) {
            $r = AdminSetting::where('name', 'notification')->update(['data'=>$request->message, 'important' => $request->status]);
        }else {
            $r = AdminSetting::create(['name'=>'notification','data'=>$request->message, 'important' => $request->status]);
        }
        if ($r) {
            Session::flash('alert',['t'=>'Success','m'=>'Transfer message settings updated successfully.']); return redirect()->back();
        }
        Session::flash('alert',['t'=>'Error','m'=>'Something went wrong, Try again.']); return redirect()->back();
    }
    
    // KYC controller
    public function manageKyc() {
        $pg = 'Manage KYC Settings';
        $levelOne = KycLevel::findOrFail(1);
        $levelTwo = KycLevel::findOrFail(2);
        $levelThree = KycLevel::findOrFail(3);
        
        // dd($levelOne);
       
        return view('admin.settings.manage-kyc', compact(['pg', 'levelOne', 'levelTwo', 'levelThree',]));
    }

}
