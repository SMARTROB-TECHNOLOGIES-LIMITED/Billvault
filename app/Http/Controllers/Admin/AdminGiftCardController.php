<?php 

namespace App\Http\Controllers\Admin;  

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use App\Models\GiftCardRate;
use App\Models\Country;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\TransactionLog;
use App\Models\User;

class AdminGiftCardController extends Controller
{
    public function index()
    {
        $giftCards = GiftCard::all();
        $countries = Country::all();
        return view('admin.giftcard.index', compact('giftCards', 'countries'));
    }

    public function store(Request $request)
    {
        
        
        $validated = $request->validate([
            'name' => 'required|string|max:255', 
            'card_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        
        // Check if an image was uploaded
        if ($request->hasFile('card_image')) {
            $image = $request->file('card_image');
            $filename = Str::random(20) . '.' . $image->getClientOriginalExtension();
            $raw = $image->storeAs('public/gift_cards', $filename);
            $validated['image_url'] = str_replace('public/', '/storage/app/public/', $raw); 
        }
        
        // dd($validated['image_url']);
    
        // Create the gift card record
        GiftCard::create([
            'name' => $validated['name'], 
            'image' => $validated['image_url'] ?? null, 
            'is_enabled' => true
        ]);
        
        Session::flash('alert',['t'=>'Success','m'=>'Gift card created successfully.']); return redirect()->back();

        
    }
    
    public function transactions()
    {
        $transactions = TransactionLog::where('type', 'Sell Gift Card')
        ->where('status', 'Pending')
        ->orderBy('created_at', 'desc')
        ->get();
        return view('admin.giftcard.gift-card-transaction', compact('transactions'));
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
            
            
            // Update the user's balance
            $user = User::find($validated['user_id']);
            $user->balance += $validated['payable'];
            $user->save();
            
            $transaction->update(['status' => 'successful']);
    
            
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

    public function update(Request $request, GiftCard $giftCard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'buy_rate' => 'required|numeric',
            'sell_rate' => 'required|numeric',
            'image_url' => 'nullable|url',
        ]);

        $giftCard->update($validated);
        return redirect()->back()->with('success', 'Gift card updated successfully.');
    }

    public function toggleStatus(GiftCard $GiftCard)
    {
        $GiftCard->update(['is_enabled' => !$GiftCard->is_enabled]);
        return redirect()->back()->with('success', 'Gift card status updated successfully.');
    }
    
    public function rates(GiftCard $giftCard)
    {
        $rates = $giftCard->rates; 
        $countries = Country::all();
        return view('admin.giftcard.rates', compact('giftCard', 'rates', 'countries'));
    }
    
    public function addRate(Request $request, GiftCard $giftCard)
    {
        // dd($request, $giftCard);
        // Validate the incoming request
        $validated = $request->validate([
            'amount_range_min' => 'required|numeric|min:0',
            'amount_range_max' => 'required|numeric|min:0',
            'rate' => 'required|numeric|min:0',
            'country' => 'required|exists:countries,id',
        ]);

        // Create a new rate for the gift card
        $giftCard->rates()->create([
            'min_amount' => $validated['amount_range_min'],
            'max_amount' => $validated['amount_range_max'],
            'rate' => $validated['rate'],
            'country_id' => $validated['country'],
        ]);

        Session::flash('alert', ['t' => 'Success', 'm' => 'Rate added successfully.']);
        return redirect()->back();
    }
    
    public function destroy($id)
    {
        try {
            
            $rate = GiftCardRate::findOrFail($id);
            
            $rate->delete();
            Session::flash('alert', ['t' => 'Success', 'm' => 'Rate deleted successfully.']);
            return redirect()->back();
        } catch (\Exception $e) {
            // Handle exceptions and redirect back with an error message
            return redirect()->back()->with('error', 'Failed to delete rate: ' . $e->getMessage());
        }
    }
   
    public function updateGiftCard(Request $request, GiftCard $giftCard)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'buy_rate' => 'nullable|numeric',
            'sell_rate' => 'nullable|numeric',
            'image_url' => 'nullable|url',
            'country' => 'nullable|string|max:255',
        ]);
    
        $giftCard->update($validated);
    
        return redirect()->back()->with('success', 'Gift card updated successfully.');
    }
}

?>