<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use App\Support\Constants\Constants;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = 12; // 12 events per page (4 rows x 3 cols)
        
        // Get filter parameters
        $search = $request->get('search');
        $month = $request->get('month');
        $year = $request->get('year');
        
        // Default to current month if no filters applied
        $now = now();
        if (!$search && !$month && !$year) {
            $month = $now->month;
            $year = $now->year;
        }
        
        // Build query
        $query = Event::query()->orderBy('event_date', 'desc');
        
        // Apply search filter
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        
        // Apply month filter
        if ($month) {
            $query->whereMonth('event_date', $month);
        }
        
        // Apply year filter
        if ($year) {
            $query->whereYear('event_date', $year);
        }
        
        // Paginate results
        $events = $query->paginate($perPage)->appends($request->except('page'));
        
        // Get available years for dropdown (from existing events)
        $availableYears = Event::selectRaw('YEAR(event_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        
        return view('admin_views.home_page', [
            'events' => $events,
            'availableYears' => $availableYears,
            'currentFilters' => [
                'search' => $search,
                'month' => $month,
                'year' => $year,
            ]
        ]);
    }

    public function storeEvent(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'event_date' => 'required|date',
                'price' => 'required|numeric|min:0',
            ],
            [
                'name.required' => "Nama Event Wajib Diisi",
                'event_date.required' => "Tanggal Event Wajib Diisi",
                'price.required' => "Harga Tiket Event Wajib Diisi",
            ]
        );

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()));
        }

        try {
            $data = $validator->safe()->all();
            $data['total_registrant'] = 0;

            Event::create($data);
            return redirect()->back()->with('success', 'Data Event Berhasil Ditambahkan');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', $th->getMessage());
        }
    }

    public function updateEvent(Request $request, string $ID)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'event_date' => 'date',
            'price' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()));
        }

        try {
            $data = $validator->safe()->all();

            $event = Event::find($ID);

            if (!isset($event)) {
                throw new Error("Data Tidak Ditemukan");
            }

            $event->update($data);
            return redirect()->back()->with('success', 'Data Event Berhasil Ditambahkan');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', $th->getMessage());
        }
    }

    public function destroyEvent(string $ID)
    {
        try {
            $event = Event::find($ID);

            if (!isset($event)) {
                throw new Error("Data Tidak Ditemukan");
            }

            $event->delete();

            return redirect()->back()->with('success', 'Data Event Dihapus');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', 'Gagal Menghapus Data Event');
        }
    }

    public function editProfile()
    {
        return view('admin_views.edit_profile');
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'nullable',
            'new_password' => 'nullable|min:6',
            'confirm_password' => 'required_with:new_password|same:new_password',
        ], [
            'new_password.min' => 'Password minimal harus :min karakter',
            'confirm_password.same' => 'Pengulangan Password Tidak Sama',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()))
                ->withInput();
        }


        try {
            $reqData = $validator->safe()->all();

            $oldData = User::find(Auth::user()->id);
            if (!isset($oldData)) throw new Exception('Data Tidak Ditemukan');

            $newData = collection::make();

            if (isset($reqData['old_password']) && isset($reqData['new_password'])) {
                if (!(Hash::check($reqData['old_password'], $oldData->password))) {
                    throw new Exception('Password Lama Tidak Valid');
                } else {
                    $newData->put('password', bcrypt($reqData['new_password']));
                }
            }

            $oldData->update($newData->toArray());

            return redirect()->route('admin.home');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', 'Gagal Menghapus Data Event');
        }
    }
}
