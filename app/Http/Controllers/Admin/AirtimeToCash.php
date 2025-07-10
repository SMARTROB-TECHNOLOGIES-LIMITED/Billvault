<?php 
    namespace App\Http\Controllers\Admin;
    
    use App\Models\AirtimeToCash;
    use Illuminate\Http\Request;
    
    class AirtimeToCashController extends Controller
    {
        public function index()
        {
            $settings = AirtimeToCash::all();
            return view('admin.airtime-cash', compact('settings'));
        }
    
        public function store(Request $request)
        {
            $request->validate([
                'network_name' => 'required|string|max:255',
                'receiver_number' => 'required|string|max:20',
                'payment_percentage' => 'required|numeric|min:0|max:100',
                'minimum_airtime' => 'required|numeric|min:0',
                'maximum_airtime' => 'required|numeric|min:0|gt:minimum_airtime',
            ]);
    
            AirtimeToCash::create($request->all());
    
            return redirect()->back()->with('success', 'Network setting created successfully!');
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
    
            return redirect()->back()->with('success', 'Network setting updated successfully!');
        }
    
        public function toggleStatus(AirtimeToCash $AirtimeToCash)
        {
            $AirtimeToCash->update(['is_enabled' => !$AirtimeToCash->is_enabled]);
    
            return redirect()->back()->with('success', 'Network status updated successfully!');
        }
    }

?>