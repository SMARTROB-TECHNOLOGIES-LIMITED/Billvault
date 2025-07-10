<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\AdminSetting;
use App\Models\TransactionLog;
use App\Models\Banner;
use App\Models\KycLevel;
use App\Models\TierTwo;
use App\Models\TierThree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\FirebaseService;
use App\Mail\KYCRejectionMail;
use Illuminate\Support\Facades\Mail;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;


class KycController extends Controller
{
    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
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
    
    public function updateLevelDetails(Request $request) {
        $level_id = $request->id;
        $level = KycLevel::findOrFail($level_id);
        
        // dd($level);

        // Update the fields based on the form data
        $level->update($request->all());
        
        Session::flash('alert',['t'=>'Success','m'=>'Account updated successfully.']); return redirect()->back();
        
    }
    
    public function levelTwoKycRequest(){
        $pg = 'Level Two Requests';

        $list = TierTwo::with('user')->latest()->get();
   
        return view('admin.kyc.level-two-request', compact(['pg','list']));
    }
    
    public function rejectleveltwo(Request $request){
        
        $validated = $request->validate([
            'kyc_id' => 'required',
            'reject_reason' => 'required|string',
        ]);
        
        $reject_reason = $validated['reject_reason'];
        
        $kyc = TierTwo::find($validated['kyc_id']);
        
        if($kyc){
            $user_id = $kyc->user_id;
            $user = User::find($user_id);
            
            $kyc->status = 2; // Declined
            $kyc->rejection_reason = $reject_reason;
            $kyc->save();
            
            $user->level_two_kyc_status = 2;
            $user->level_two_rejected_status = $reject_reason;
            $user->save();
            
            $emailStatus = 'rejected';
            $level = 'Level Two';
            
            Mail::to($user->email)->send(new KYCRejectionMail($user->surname, $emailStatus, $reject_reason, $level));
            $deviceToken = $user->device_token;
            if(!is_null($deviceToken)){
                $title = "KYC Update";
                $body = "$level KYC $emailStatus";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            
            Session::flash('alert',['t'=>'Success','m'=>'Request has been rejected.']); return redirect()->back();
        }else{
            Session::flash('alert',['t'=>'Error','m'=>'KYC not found.']); return redirect()->back();
        }
        
    
        
    }
    
    public function approveleveltwo(Request $request){
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:tier_twos,id', 
        ]);
    
        // dd($validated['user_id']);
        $user = TierTwo::find($validated['user_id']);
    
        if ($user) {
            $user_id = $user->user_id;
            $account = User::find($user_id);
            $user->status = 1; 
            $user->save();
            
            $account->level_two_kyc_status = 1;
            $account->dob = $user->date_of_birth;
            $account->bvn = $user->bvn;
            $account->account_level = 2;
            $account->is_account_restricted = 0;
            $account->save();
            
            $emailStatus = 'approved';
            $level = 'Level Two';
            
            $deviceToken = $account->device_token;
            if(!is_null($deviceToken)){
                $title = "KYC Update";
                $body = "$level KYC $emailStatus";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            Mail::to($account->email)->send(new KYCRejectionMail($account->surname, $emailStatus, '', $level));
    
            Session::flash('alert',['t'=>'Success','m'=>'Request has been approved.']); return redirect()->back();
        } else {
            Session::flash('alert',['t'=>'Error','m'=>'User not found.']); return redirect()->back();
        }
    }

    public function levelThreeKycRequest (){
        $pg = 'Level Three Requests';

        $list = TierThree::with('user')->latest()->get();
   
        return view('admin.kyc.level-three-request', compact(['pg','list']));
    }
    
    public function rejectlevelthree(Request $request){
        $user_id = $request->user_id;
        $reject_reason = $request->reject_reason;
        
        // Find the user and update status
        $user = TierThree::find($user_id);
        if($user){
            $user_id = $user->user_id;
            $account = User::find($user_id);
            $user->status = 2; // Declined
            $user->rejection_reason = $reject_reason;
            $user->save();
            
            $account->level_three_kyc_status = 2;
            $account->level_three_rejected_status = $reject_reason;
            $account->save();
            
            $emailStatus = 'rejected';
            $level = 'Level Three';
            
            $deviceToken = $account->device_token;
            if(!is_null($deviceToken)){
                $title = "KYC Update";
                $body = "$level KYC $emailStatus";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            Mail::to($account->email)->send(new KYCRejectionMail($account->surname, $emailStatus, $reject_reason, $level));
            Session::flash('alert',['t'=>'Success','m'=>'Request has been rejected.']); return redirect()->back();
        }
        
    
        Session::flash('alert',['t'=>'Success','m'=>'Request has been rejected.']); return redirect()->back();
    }
    
    public function approvelevelthree(Request $request){
        // Validate the request
        $validated = $request->validate([
            'user_id' => 'required|exists:tier_threes,id', 
        ]);
    
        // dd($validated['user_id']);
        $user = TierThree::find($validated['user_id']);
    
        if ($user) {
            $user_id = $user->user_id;
            $account = User::find($user_id);
            // dd($account, $user);
            $user->status = 1; 
            $user->save();
            
            
            $account->address = $user->house_address;
            $account->account_level = 3;
            $account->is_account_restricted = 0;
            $account->level_three_kyc_status = 1;
            $account->save();
    
            $emailStatus = 'approved';
            $level = 'Level Three';
            
            $deviceToken = $account->device_token;
            if(!is_null($deviceToken)){
                $title = "KYC Update";
                $body = "$level KYC $emailStatus";
                $this->firebaseService->sendNotification($title, $body, $deviceToken );
            }
            
            Mail::to($account->email)->send(new KYCRejectionMail($account->surname, $emailStatus, '', $level));
            Session::flash('alert',['t'=>'Success','m'=>'Request has been approved.']); return redirect()->back();
        } else {
            Session::flash('alert',['t'=>'Error','m'=>'User not found.']); return redirect()->back();
        }
    }
    
    public function manualLevelTwo(){
        $pg = 'User Top up';

        $list = DB::table('users as f')
            ->select('f.*')
            ->where('f.complete', 1) // Filter users where complete = 1
            ->latest()
            ->get();

        return view('admin.kyc.manual-level-two', compact(['pg','list']));
    }
    
    public function submitManualLevelTwo(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'date_of_birth' => 'required|date',
            'id_front' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'bvn' => 'required|digits:11',
            'id_type' => 'required',
            'passport' => 'required|image|mimes:jpeg,png,jpg,gif|max:20120'
        ];
    
        $messages = [
            'user_id.required' => 'User selection is required',
            'date_of_birth.required' => 'Date of birth is required',
            'id_type.required' => 'ID type is required',
            'id_front.required' => 'Front ID image is required',
            'bvn.required' => 'BVN is required',
            'bvn.digits' => 'BVN must be exactly 11 digits',
            'passport.required' => 'Passport photo is required',
            'passport.image' => 'Passport must be an image',
            'passport.mimes' => 'Passport must be jpeg, jpg, png or gif',
            'passport.max' => 'Passport max size is 20MB',
        ];
    
        $validatedData = Validator::make($request->all(), $rules, $messages);
    
        if ($validatedData->fails()) {
            return redirect()->back()->withErrors($validatedData)->withInput();
        }
    
        $user = User::find($request->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'Selected user not found.');
        }
    
        $existingTierTwo = TierTwo::where('user_id', $user->id)
            ->whereIn('status', [0, 1])
            ->first();
    
        if ($existingTierTwo) {
            return redirect()->back()->with('error', 'User already has a pending or approved Tier 2 request.');
        }
    
        // Upload ID images
        $frontPath = $request->file('id_front')->storeAs('ids', Str::uuid() . '.' . $request->file('id_front')->extension(), 'public');
        $backPath = $request->hasFile('id_back')
            ? $request->file('id_back')->storeAs('ids', Str::uuid() . '.' . $request->file('id_back')->extension(), 'public')
            : null;
    
        // Handle Passport Photo
        $passport = $request->file('passport');
        $passportName = $user->username . '_passport_' . Carbon::now()->timestamp . '.' . $passport->getClientOriginalExtension();
        $passportRawPath = $passport->storeAs('public/passports', $passportName);
        $passportFullPath = storage_path('app/' . $passportRawPath);
        $passportPublicPath = '/storage' . str_replace(storage_path(), '', $passportFullPath);
    
        // Resize image
        $passportImage = Image::make($passportFullPath)->resize(300, 300);
        $passportImage->save($passportFullPath);
    
        // Optionally delete old passport if updating
        if (!str_contains($user->passport, 'default.jpg') && File::exists(public_path($user->passport))) {
            File::delete(public_path($user->passport));
        }
    
        // Save to user
        $user->passport = str_replace("\\", "/", $passportPublicPath);
        $user->level_two_kyc_status = 1;
        $user->dob = $request->date_of_birth;
        $user->bvn = $request->bvn;
        $user->account_level = 2;
        $user->is_account_restricted = 0;
        $user->save();
    
        // Save Tier 2 record
        TierTwo::create([
            'user_id' => $user->id,
            'date_of_birth' => $request->date_of_birth,
            'bvn' => $request->bvn,
            'status' => 1,
            'id_front' => $frontPath,
            'id_back' => $backPath ?? "",
            'id_type' => $request->id_type,
        ]);
    
        // Notify and Email
        $emailStatus = 'approved';
        $level = 'Level Two';
    
        if (!is_null($user->device_token)) {
            $title = "KYC Update";
            $body = "$level KYC $emailStatus";
            $this->firebaseService->sendNotification($title, $body, $user->device_token);
        }
    
        Mail::to($user->email)->send(new KYCRejectionMail($user->surname, $emailStatus, '', $level));
    
        Session::flash('alert', ['t' => 'Success', 'm' => 'Tier 2 KYC request has been approved.']);
        return redirect()->back();
    }
    
    public function submitManualLevelThree(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'house_address' => 'required|string|max:255',
            'utility_bill' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    
        $messages = [
            'user_id.required' => 'User selection is required',
            'house_address.required' => 'House address is required',
            'utility_bill.required' => 'Utility bill image is required',
        ];
    
        $validatedData = Validator::make($request->all(), $rules, $messages);
    
        if ($validatedData->fails()) {
            return redirect()->back()
                ->withErrors($validatedData)
                ->withInput();
        }
    
        $user = User::find($request->user_id);
        if (!$user) {
            return redirect()->back()->with('error', 'Selected user not found.');
        }
    
        $existingTierThree = TierThree::where('user_id', $user->id)
            ->whereIn('status', [0, 1])
            ->first();
    
        if ($existingTierThree) {
            return redirect()->back()->with('error', 'User already has a pending or approved Tier 3 request.');
        }
    
        // Upload utility bill
        $utilityBillPath = $request->file('utility_bill')->storeAs(
            'ids',
            Str::uuid() . '.' . $request->file('utility_bill')->extension(),
            'public'
        );
    
        // Create Tier 3 record
        TierThree::create([
            'user_id' => $user->id,
            'house_address' => $request->house_address,
            'utility_bill' => $utilityBillPath,
            'status' => 1, // approved
        ]);
    
        // Update user
        $user->level_three_kyc_status = 1;
        $user->address = $request->house_address;
        $user->account_level = 3;
        $user->is_account_restricted = 0;
        $user->save();
    
        // Send notification
        $emailStatus = 'approved';
        $level = 'Level Three';
    
        if (!is_null($user->device_token)) {
            $title = "KYC Update";
            $body = "$level KYC $emailStatus";
            $this->firebaseService->sendNotification($title, $body, $user->device_token);
        }
    
        // Send email
        Mail::to($user->email)->send(new KYCRejectionMail($user->surname, $emailStatus, '', $level));
    
        Session::flash('alert', ['t' => 'Success', 'm' => 'Tier 3 request has been approved.']);
        return redirect()->back();
    }

}

?>