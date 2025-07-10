<?php

    namespace App\Http\Controllers\Admin;
    
    use App\Models\User;
    use App\Models\AirtimeToCash;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use App\Http\Controllers\Controller;
    use Stevebauman\Purify\Facades\Purify;
    use Illuminate\Support\Facades\Session;
    use Illuminate\Support\Facades\Validator;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Models\TransactionLog;
    
    
    class AirtimeToCashController extends Controller
    {
        public function index()
        {
            $settings = AirtimeToCash::all();
            return view('admin.airtime-cash', compact('settings'));
        }
        
        public function transactions()
        {
            $transactions = TransactionLog::where('type', 'ATC')
            ->where('status', 'Pending')
            ->orderBy('created_at', 'desc')
            ->get();
            return view('admin.airtime-cash-transactions', compact('transactions'));
        }
    
        public function update(Request $request, AirtimeToCash $AirtimeToCash)
        {
            $request->validate([
                'network_name' => 'required|string|max:255',
                'receiver_number' => 'required|string|max:20',
                'payment_percentage' => 'required|numeric|min:0|max:100',
                'minimum_airtime' => 'required|numeric|min:0',
                'maximum_airtime' => 'required|numeric|min:0|gt:minimum_airtime',
            ]);
    
            $AirtimeToCash->update($request->all());
    
            Session::flash('alert',['t'=>'Success','m'=>'Network settings updated successfully!']); return redirect()->back();
        }
    
        public function toggleStatus(AirtimeToCash $AirtimeToCash)
        {
            
            $update = $AirtimeToCash->update(['is_enabled' => !$AirtimeToCash->is_enabled]);
            
            Session::flash('alert',['t'=>'Success','m'=>'Network status updated successfully!']); return redirect()->back();
        }
        

        public function decline(TransactionLog $transactionLog)
        {
            // Update the status to 'decline'
            $update = $transactionLog->update(['status' => 'Decline']);
        
            // Flash success message
            if ($update) {
                Session::flash('alert', ['t' => 'Success', 'm' => 'Transaction declined successfully!']);
            } else {
                Session::flash('alert', ['t' => 'Error', 'm' => 'Failed to decline the transaction.']);
            }
        
            return redirect()->back();
        }
        
        public function approveTransaction(Request $request, TransactionLog $transaction)
        {
            // Validate the request
            $validated = $request->validate([
                'payable' => 'required|numeric',
                'user_id' => 'required|exists:users,id',
            ]);
        
            
            try {
                // Update transaction status to successful 
                $transaction->update(['status' => 'successful']);
        
                // Update the user's balance
                $user = User::find($validated['user_id']);
                $user->balance += $validated['payable'];
                $user->save();
        
                
                // Flash success message
                Session::flash('alert', ['t' => 'Success', 'm' => 'Transaction approved and user balance updated successfully!']);
        
            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollBack();
        
                // Flash error message
                Session::flash('alert', ['t' => 'Error', 'm' => 'An error occurred while processing the transaction.']);
            }
        
            // Redirect back
            return redirect()->back();
        }
        
    }

?>