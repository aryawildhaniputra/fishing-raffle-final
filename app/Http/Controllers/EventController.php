<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Participant;
use App\Models\ParticipantGroup;
use App\Support\Constants\Constants;
use App\Support\Enums\ConfirmDrawTypeEnum;
use App\Support\Enums\ParticipantGroupRaffleStatusEnum;
use App\Support\Enums\ParticipantGroupStatusEnum;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParticipantGroupsImport;
use App\Exports\ParticipantGroupsTemplateExport;

class EventController extends Controller
{
    public function index(string $ID)
    {
        $event = Event::with(['groups.participants'])->withCount(['groups'])->find($ID);

        $groupCollect = collect($event->groups->toArray());
        $groups_not_yet_drawn = $groupCollect
            ->whereIn("status", ["dp", "paid"])
            ->where("raffle_status", "not_yet")
            ->sortBy([
                ['total_member', 'desc'],   // Terbesar dulu (5, 4, 3, 2, 1)
                ['id', 'asc'],              // ID terkecil dulu (yang daftar lebih dulu)
            ])
            ->values();

        $groups_drawn = $groupCollect->where("raffle_status", "completed")->sortByDesc("updated_at")->values();

        $eventParticipants = Participant::where('event_id', $event->id)->orderBy('stall_number', 'asc')->get();

        $participantsData = collect();

        for ($i = 0; $i < Constants::MAX_STALLS; $i++) {
            $stallNumber = $i + 1;
            $participantName = $eventParticipants->where('stall_number', $stallNumber)->first()?->name;
            $participantsData->push([
                "stall_number" => $stallNumber,
                "participant_name" => isset($participantName) ? $participantName : "-",
                "isBooked" => isset($participantName) ? true : false
            ]);
        }

        $allParticipantsData = $participantsData;

        $splitParticipantsData = $participantsData->split(2);

        return view('admin_views.detail_event', [
            'event' => $event,
            'participantGroups' => $event->groups, // For duplicate name validation
            'groups_not_yet_drawn' => $groups_not_yet_drawn,
            'groups_drawn' => $groups_drawn,
            'participants' => $allParticipantsData,
            'rightColumnParticipant' => $splitParticipantsData->first()->sortByDesc('stall_number'),
            'leftColumnParticipant' => $splitParticipantsData->last(),
        ]);
    }

    public function storeParticipantGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'event_id' => 'required|string',
            'phone_num' => 'nullable|string',
            'total_member' => 'required|numeric|min:0',
            'status' => 'required|in:unpaid,dp,paid',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()));
        }

        try {
            $data = $validator->safe()->all();
            
            // Set default value '-' if phone_num is empty (trim whitespace)
            if (!isset($data['phone_num']) || trim($data['phone_num']) === '') {
                $data['phone_num'] = '-';
            }

            $event = Event::find($data['event_id']);

            if (!isset($event)) {
                throw new Error("Data Tidak Ditemukan");
            }

            $existedParticipant = ParticipantGroup::where('event_id', $event->id)->where('name', $data['name'])->first();

            if ($existedParticipant)
                throw new Error("Nama Grup Telah Digunakan");

            $totalRegister = $event->total_registrant + $data['total_member'];

            if ($totalRegister > Constants::MAX_STALLS) {
                throw new Error("Kuota Lapak Tidak Mencukupi");
            }
            DB::beginTransaction();
            ParticipantGroup::create($data);
            $event->update([
                "total_registrant" => $totalRegister
            ]);
            DB::commit();

            return redirect()->back()->with('success', 'Data Pendaftar Berhasil Ditambahkan');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', $th->getMessage());
        }
    }

    public function getParticipantGroupByID(string $ID)
    {
        $data = ParticipantGroup::find($ID);

        if (!$data) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        return response()->json([
            'data' => $data
        ]);
    }

    public function updateParticipantGroup(Request $request, string $ID)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'phone_num' => 'nullable|string',
            'total_member' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:' . ParticipantGroupStatusEnum::UNPAID->value . ',' . ParticipantGroupStatusEnum::DP->value . ',' . ParticipantGroupStatusEnum::PAID->value,
        ]);

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()));
        }

        try {
            $newDataGroupParticipant = $validator->safe()->all();
            
            // Set default value '-' if phone_num is empty (trim whitespace)
            if (!isset($newDataGroupParticipant['phone_num']) || trim($newDataGroupParticipant['phone_num']) === '') {
                $newDataGroupParticipant['phone_num'] = '-';
            }
            
            $newDataEvent = Collection::Make();

            $participantGroup = ParticipantGroup::find($ID);

            if (!$participantGroup) {
                throw new Error("Data Grup Tidak Ditemukan");
            }

            $event = Event::find($participantGroup->event_id);

            if (!isset($event)) {
                throw new Error("Data Event Tidak Ditemukan");
            }

            if ($participantGroup->status == ParticipantGroupStatusEnum::COMPLETED->value) {
                throw new Error("Data Yang sudah selesai diundi tidak dapat diedit");
            }

            if ($participantGroup->total_member != $newDataGroupParticipant['total_member']) {
                $NewTotalRegister = ($event->total_registrant - $participantGroup->total_member) + $newDataGroupParticipant['total_member'];

                if ($NewTotalRegister > Constants::MAX_STALLS) {
                    throw new Error("Kuota Lapak Tidak Mencukupi");
                }
                $newDataEvent->put('total_registrant', $NewTotalRegister);
            }


            DB::beginTransaction();
            $participantGroup->update($newDataGroupParticipant);

            if ($newDataEvent->count() > 0) {
                $event->update($newDataEvent->toArray());
            }
            DB::commit();

            return redirect()->back()->with('success', 'Data Pendaftar Berhasil Diperbarui');
        } catch (\Throwable $th) {
            return back()
                ->with('errors', $th->getMessage());
        }
    }

    public function destroyParticipantGroupByID(string $ID)
    {
        try {
            $participantGroup = ParticipantGroup::find($ID);

            if (!isset($participantGroup)) {
                throw new Error("Data Tidak Ditemukan");
            }

            // Ambil event terkait
            $event = Event::find($participantGroup->event_id);

            if (!isset($event)) {
                throw new Error("Data Event Tidak Ditemukan");
            }

            // Mulai transaction
            DB::beginTransaction();

            // Hapus semua participants terkait (nomor lapak) terlebih dahulu
            // Ini akan membebaskan nomor lapak untuk digunakan lagi
            Participant::where('participant_groups_id', $participantGroup->id)->delete();

            // Kurangi total_registrant di event dengan jumlah total_member yang dihapus
        // Gunakan max(0, ...) untuk mencegah nilai negatif jika data corrupt
        $newTotalRegistrant = max(0, $event->total_registrant - $participantGroup->total_member);
        $event->update([
            'total_registrant' => $newTotalRegistrant
        ]);
            // Hapus participant group
            $participantGroup->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Data Pendaftar dan Nomor Lapak Telah Dihapus');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()
                ->with('errors', 'Gagal Menghapus Data Pendaftar: ' . $th->getMessage());
        }
    }

    public function drawStall(string $ID)
    {
        try {
            $participantGroup = ParticipantGroup::find($ID);

            if (!$participantGroup)
                throw new Error("Data Grup Tidak Ditemukan");

            if ($participantGroup->raffle_status === ParticipantGroupRaffleStatusEnum::COMPLETED->value)
                throw new Error("Grup Telah Diundi");

            $totalMembers = $participantGroup->total_member;
            
            // Ambil nomor lapak yang sudah dibooking
            $bookedStalls = Participant::where('event_id', $participantGroup->event_id)
                ->get()
                ->pluck('stall_number')
                ->toArray();

            // Semua nomor lapak (1-222)
            $allStalls = range(1, Constants::MAX_STALLS);
            
            // Nomor lapak yang masih tersedia
            $availableStalls = array_diff($allStalls, $bookedStalls);
            
            if (count($availableStalls) < $totalMembers) {
                throw new Error("Lapak tersedia tidak cukup untuk grup ini");
            }

            // Cari nomor tengah yang valid dengan scoring
            $validMiddleNumbers = [];
            $skippedByEdgeOnly = []; // Track kandidat yang di-skip karena edge-only
            
            foreach ($availableStalls as $middleNumber) {
                $canUpper = $this->canSelectUpper($middleNumber, $totalMembers, $availableStalls);
                $canUnder = $this->canSelectUnder($middleNumber, $totalMembers, $availableStalls);
                
                // VALIDASI KETAT: Grup 2-5 anggota HARUS dapat slot berjejer
                // Nomor tengah valid jika minimal salah satu (UPPER atau UNDER) bisa dipilih
                // Ini memastikan grup 2-5 PASTI dapat nomor berjejer (consecutive)
                // Grup 1 anggota tidak perlu validasi ini (bisa slot mana saja)
                if ($canUpper || $canUnder) {
                    // EDGE-ONLY SELECTION untuk grup 2-3 anggota
                    // Jika grup 2-3 dan ada consecutive range == 4 atau == 6, hanya pilih edge (ujung)
                    $passedEdgeOnly = true;
                    if ($totalMembers == 2 || $totalMembers == 3) {
                        $isEdgeCandidate = $this->isEdgeCandidate($middleNumber, $availableStalls, $canUpper, $canUnder);
                        if (!$isEdgeCandidate) {
                            $passedEdgeOnly = false;
                            // Simpan untuk fallback
                            $skippedByEdgeOnly[] = [
                                'middle' => $middleNumber,
                                'canUpper' => $canUpper,
                                'canUnder' => $canUnder,
                            ];
                            continue; // Skip kandidat non-edge untuk grup 2-3
                        }
                    }
                    
                    // VALIDASI LOOKAHEAD: Pastikan pilihan ini menyisakan slot berjejer untuk grup lain
                    // Simulasi jika kandidat ini dipilih, apakah sisa slot cukup untuk grup yang belum diundi?
                    $passesLookahead = $this->validateLookahead(
                        $middleNumber, 
                        $totalMembers, 
                        $availableStalls, 
                        $canUpper, 
                        $canUnder,
                        $participantGroup->event_id
                    );
                    
                    // Skip kandidat yang tidak lolos validasi lookahead
                    if (!$passesLookahead) {
                        continue;
                    }
                    
                    // Hitung score berdasarkan sisa slot berjejer terpanjang
                    // Score lebih tinggi untuk kandidat yang bisa UPPER DAN UNDER (prioritas)
                    $score = $this->calculateSlotScore($middleNumber, $totalMembers, $availableStalls, $canUpper, $canUnder);
                    
                    $validMiddleNumbers[] = [
                        'middle' => $middleNumber,
                        'canUpper' => $canUpper,
                        'canUnder' => $canUnder,
                        'score' => $score,
                    ];
                }
            }
            
            // FALLBACK: Jika edge-only filtering membuat semua kandidat hilang
            // Coba lagi dengan kandidat yang di-skip oleh edge-only
            if (empty($validMiddleNumbers) && !empty($skippedByEdgeOnly)) {
                foreach ($skippedByEdgeOnly as $candidate) {
                    // Validasi lookahead untuk kandidat fallback
                    $passesLookahead = $this->validateLookahead(
                        $candidate['middle'], 
                        $totalMembers, 
                        $availableStalls, 
                        $candidate['canUpper'], 
                        $candidate['canUnder'],
                        $participantGroup->event_id
                    );
                    
                    if ($passesLookahead) {
                        $score = $this->calculateSlotScore(
                            $candidate['middle'], 
                            $totalMembers, 
                            $availableStalls, 
                            $candidate['canUpper'], 
                            $candidate['canUnder']
                        );
                        
                        $validMiddleNumbers[] = [
                            'middle' => $candidate['middle'],
                            'canUpper' => $candidate['canUpper'],
                            'canUnder' => $candidate['canUnder'],
                            'score' => $score,
                        ];
                    }
                }
            }
            
            // FALLBACK LEVEL 2: Jika masih kosong, relax lookahead validation juga
            // Gunakan kandidat dari skippedByEdgeOnly tanpa lookahead validation
            if (empty($validMiddleNumbers) && !empty($skippedByEdgeOnly)) {
                foreach ($skippedByEdgeOnly as $candidate) {
                    $score = $this->calculateSlotScore(
                        $candidate['middle'], 
                        $totalMembers, 
                        $availableStalls, 
                        $candidate['canUpper'], 
                        $candidate['canUnder']
                    );
                    
                    $validMiddleNumbers[] = [
                        'middle' => $candidate['middle'],
                        'canUpper' => $candidate['canUpper'],
                        'canUnder' => $candidate['canUnder'],
                        'score' => $score,
                    ];
                }
            }

            // FALLBACK: Jika tidak ada slot berjejer
            if (empty($validMiddleNumbers)) {
                // HANYA grup 1 anggota yang boleh menggunakan scattered slots
                // Grup 2-5 anggota HARUS mendapat slot berjejer
                if ($totalMembers == 1) {
                    // Coba ambil slot scattered sebagai fallback untuk grup 1 anggota
                    $scatteredSlots = $this->selectScatteredSlots($availableStalls, $totalMembers);
                    
                    if (empty($scatteredSlots)) {
                        throw new Error("Tidak ada nomor valid yang bisa diundi");
                    }
                    
                    // Return scattered slots dengan format khusus
                    return response()->json([
                        'data' => [
                            'participant_group_id' => $participantGroup->id,
                            'total_member' => $totalMembers,
                            'middle' => null,
                            'upper' => null,
                            'under' => null,
                            'canUpper' => false,
                            'canUnder' => false,
                            'randomStallNumber' => $scatteredSlots,
                            'isScattered' => true, // Flag untuk frontend
                        ],
                    ]);
                } else {
                    // Grup 2-5 anggota HARUS berjejer, tidak boleh scattered
                    // Berikan error message yang lebih detail untuk debugging
                    $availableCount = count($availableStalls);
                    $errorMsg = "Tidak ada slot berjejer yang tersedia untuk grup dengan {$totalMembers} anggota. ";
                    $errorMsg .= "Slot tersedia: {$availableCount}. ";
                    $errorMsg .= "Mohon atur ulang pengundian atau hubungi administrator.";
                    throw new Error($errorMsg);
                }
            }

            // Sort berdasarkan score tertinggi (sisa slot berjejer terpanjang)
        usort($validMiddleNumbers, function($a, $b) {
            return $b['score'] - $a['score'];
        });

        /* ========== OLD LOGIC: PERCENTAGE-BASED ALLOCATION (COMMENTED OUT) ==========
        // LOGIKA ADAPTIF: Variasi berdasarkan jumlah slot tersedia
        // Hitung persentase slot yang masih tersedia
        $availablePercentage = (count($availableStalls) / Constants::MAX_STALLS) * 100;
        
        // Fase undian dengan variasi yang lebih banyak:
        // Di awal undian (>80% slot kosong): Gunakan top 40% untuk variasi maksimal
        // Di awal-tengah (60-80% slot kosong): Gunakan top 30% untuk variasi besar
        // Di tengah undian (40-60% slot kosong): Gunakan top 20% untuk balance
        // Di akhir undian (<40% slot kosong): Gunakan strict optimality (hanya score tertinggi)
        
        if ($availablePercentage > 80) {
            // Awal undian: Variasi maksimal (termasuk semua angka ratusan)
            $topPercentage = 0.40; // Top 40%
        } elseif ($availablePercentage > 60) {
            // Awal-tengah undian: Variasi besar
            $topPercentage = 0.30; // Top 30%
        } elseif ($availablePercentage > 40) {
            // Tengah undian: Moderate variasi
            $topPercentage = 0.20; // Top 20%
        } else {
            // Akhir undian: Strict optimality
            $topPercentage = 0; // Hanya score tertinggi
        }
        
        if ($topPercentage > 0) {
            // Ambil top X% kandidat terbaik untuk variasi
            $topCount = max(1, (int)(count($validMiddleNumbers) * $topPercentage));
            $topCandidates = array_slice($validMiddleNumbers, 0, $topCount);
        } else {
            // Strict: Ambil HANYA kandidat dengan score tertinggi
            $maxScore = $validMiddleNumbers[0]['score'];
            $topCandidates = array_filter($validMiddleNumbers, function($candidate) use ($maxScore) {
                return $candidate['score'] >= $maxScore;
            });
        }

        // Random pilih dari kandidat terbaik
        $selectedDraw = $topCandidates[array_rand($topCandidates)];
        ========== END OLD LOGIC ========== */

        // ENHANCED LOGIC: STRATIFIED RANDOM SAMPLING untuk persebaran merata
        // Bagi kandidat ke dalam strata berdasarkan range angka, lalu pilih dari top 30% tiap strata
        // Ini memastikan angka tersebar merata (tidak menumpuk di range tertentu)
        
        // Tentukan range untuk stratifikasi
        $maxStalls = Constants::MAX_STALLS;
        $strataCount = 3; // Bagi jadi 3 strata: low, mid, high
        $strataSize = $maxStalls / $strataCount;
        
        // Kelompokkan kandidat berdasarkan strata
        $strata = [];
        for ($i = 0; $i < $strataCount; $i++) {
            $strata[$i] = [];
        }
        
        foreach ($validMiddleNumbers as $candidate) {
            $middle = $candidate['middle'];
            $strataIndex = min((int)($middle / $strataSize), $strataCount - 1);
            $strata[$strataIndex][] = $candidate;
        }
        
        // Pilih random dari top 30% di SETIAP strata
        $selectedCandidates = [];
        foreach ($strata as $stratumCandidates) {
            if (empty($stratumCandidates)) {
                continue; // Skip strata kosong
            }
            
            // Sort by score dalam strata ini
            usort($stratumCandidates, function($a, $b) {
                return $b['score'] - $a['score'];
            });
            
            // Ambil top 30% dari strata ini
            $topPercentage = 0.30;
            $topCount = max(1, (int)(count($stratumCandidates) * $topPercentage));
            $topInStrata = array_slice($stratumCandidates, 0, $topCount);
            
            // Tambahkan ke pool kandidat
            $selectedCandidates = array_merge($selectedCandidates, $topInStrata);
        }
        
        // Jika tidak ada kandidat (edge case), fallback ke semua kandidat
        if (empty($selectedCandidates)) {
            $selectedCandidates = $validMiddleNumbers;
        }
        
        // Random pilih dari pool kandidat yang sudah stratified
        $selectedDraw = $selectedCandidates[array_rand($selectedCandidates)];
            $middle = $selectedDraw['middle'];
            $canUpper = $selectedDraw['canUpper'];
            $canUnder = $selectedDraw['canUnder'];

            // Generate nomor UPPER dan UNDER
            $upper = $canUpper ? $this->generateUpperNumbers($middle, $totalMembers, $availableStalls) : null;
            $under = $canUnder ? $this->generateUnderNumbers($middle, $totalMembers, $availableStalls) : null;

            // Jika hanya 1 anggota, tidak perlu pilih UPPER/UNDER
            if ($totalMembers == 1) {
                $upper = null;
                $under = null;
            }

            return response()->json([
                'data' => [
                    'participant_group_id' => $participantGroup->id,
                    'total_member' => $totalMembers,
                    'middle' => $middle,
                    'upper' => $upper,
                    'under' => $under,
                    'canUpper' => $canUpper,
                    'canUnder' => $canUnder,
                    'randomStallNumber' => $upper && $under ? array_merge($upper, $under) : [$middle],
                ],
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 404);
        }
    }

    /**
     * Hitung score berdasarkan sisa slot berjejer terpanjang
     * Score lebih tinggi = lebih baik (menyisakan slot berjejer lebih panjang)
     * BONUS BESAR untuk kandidat yang bisa pilih UNDER/UPPER
     */
    private function calculateSlotScore($middleNumber, $totalMembers, $availableStalls, $canUpper, $canUnder)
    {
        $maxStalls = Constants::MAX_STALLS;
        $score = 0;

        // Cek grup yang belum diundi untuk setiap ukuran (1-5 anggota)
        $undrawnGroupSizes = $this->getUndrawnGroupSizes();

        // Simulasi jika pilih UPPER
        if ($canUpper) {
            $upperNumbers = $this->generateUpperNumbers($middleNumber, $totalMembers, $availableStalls);
            $remainingAfterUpper = array_diff($availableStalls, $upperNumbers);
            $longestGapUpper = $this->findLongestConsecutiveGap($remainingAfterUpper, $maxStalls);
            
            // Penalty untuk gap yang tidak bisa diisi (1-5)
            $penaltyUpper = $this->calculateGapPenalty($remainingAfterUpper, $undrawnGroupSizes);
            $longestGapUpper -= $penaltyUpper;
            
            $score = max($score, $longestGapUpper);
        }

        // Simulasi jika pilih UNDER
        if ($canUnder) {
            $underNumbers = $this->generateUnderNumbers($middleNumber, $totalMembers, $availableStalls);
            $remainingAfterUnder = array_diff($availableStalls, $underNumbers);
            $longestGapUnder = $this->findLongestConsecutiveGap($remainingAfterUnder, $maxStalls);
            
            // Penalty untuk gap yang tidak bisa diisi (1-5)
            $penaltyUnder = $this->calculateGapPenalty($remainingAfterUnder, $undrawnGroupSizes);
            $longestGapUnder -= $penaltyUnder;
            
            $score = max($score, $longestGapUnder);
        }

        // BONUS BESAR untuk kandidat yang bisa pilih UNDER/UPPER
        // Ini memastikan user punya pilihan dan distribusi lebih merata
        if ($canUpper && $canUnder) {
            $score += 1000; // BONUS BESAR untuk kedua opsi tersedia
        } elseif ($canUpper || $canUnder) {
            $score += 500; // BONUS SEDANG untuk satu opsi tersedia
        }

        return $score;
    }

    /**
     * Dapatkan ukuran grup yang belum diundi
     * Return: array dengan key = total_member, value = true jika ada grup dengan ukuran tersebut
     */
    private function getUndrawnGroupSizes()
    {
        $sizes = [];
        
        // Cek untuk setiap ukuran 1-5
        for ($i = 1; $i <= 5; $i++) {
            $exists = ParticipantGroup::where('total_member', $i)
                ->where('raffle_status', ParticipantGroupRaffleStatusEnum::NOT_YET->value)
                ->exists();
            
            $sizes[$i] = $exists;
        }
        
        return $sizes;
    }

    /**
     * Hitung total penalty berdasarkan gap yang tidak bisa diisi
     */
    private function calculateGapPenalty($availableStalls, $undrawnGroupSizes)
    {
        $penalty = 0;
        
        // Cari semua gap dengan panjang 1-5
        $gaps = $this->findAllGaps($availableStalls);
        
        foreach ($gaps as $gapLength) {
            // Jika gap dengan panjang ini tidak bisa diisi (tidak ada grup dengan ukuran tersebut)
            if ($gapLength >= 1 && $gapLength <= 5 && !$undrawnGroupSizes[$gapLength]) {
                // Penalty lebih besar untuk gap yang lebih kecil
                $penalty += (6 - $gapLength) * 10; // Gap 1 = 50, Gap 2 = 40, Gap 3 = 30, dst
            }
        }
        
        return $penalty;
    }

    /**
     * Cari semua gap berjejer dan return panjangnya
     */
    private function findAllGaps($availableStalls)
    {
        if (empty($availableStalls)) {
            return [];
        }

        $available = array_values($availableStalls);
        sort($available);

        $gaps = [];
        $currentGap = 1;

        for ($i = 1; $i < count($available); $i++) {
            if ($available[$i] == $available[$i - 1] + 1) {
                $currentGap++;
            } else {
                // Simpan gap yang sudah selesai
                $gaps[] = $currentGap;
                $currentGap = 1;
            }
        }
        
        // Simpan gap terakhir
        $gaps[] = $currentGap;

        return $gaps;
    }

    /**
     * Cek apakah masih ada grup dengan 1 anggota yang belum diundi
     */
    private function hasUndrawnGroupWithOneMember()
    {
        // Ambil event_id dari participant group yang sedang diproses
        // Untuk sementara, kita cek dari semua event
        $hasGroup = ParticipantGroup::where('total_member', 1)
            ->where('raffle_status', ParticipantGroupRaffleStatusEnum::NOT_YET->value)
            ->exists();
        
        return $hasGroup;
    }

    /**
     * Cek apakah ada gap dengan panjang 1 (slot sendirian)
     */
    private function hasGapOfOne($availableStalls)
    {
        if (empty($availableStalls)) {
            return false;
        }

        $available = array_values($availableStalls);
        sort($available);

        // Cek setiap nomor apakah sendirian (tidak ada tetangga)
        for ($i = 0; $i < count($available); $i++) {
            $current = $available[$i];
            $hasPrevNeighbor = ($i > 0 && $available[$i - 1] == $current - 1);
            $hasNextNeighbor = ($i < count($available) - 1 && $available[$i + 1] == $current + 1);
            
            // Jika tidak ada tetangga kiri dan kanan, berarti sendirian (gap = 1)
            if (!$hasPrevNeighbor && !$hasNextNeighbor) {
                return true;
            }
        }

        return false;
    }


    /**
     * Cari panjang gap berjejer terpanjang dari slot yang tersisa
     */
    private function findLongestConsecutiveGap($availableStalls, $maxStalls)
    {
        if (empty($availableStalls)) {
            return 0;
        }

        $available = array_values($availableStalls);
        sort($available);

        $longestGap = 1;
        $currentGap = 1;

        for ($i = 1; $i < count($available); $i++) {
            if ($available[$i] == $available[$i - 1] + 1) {
                $currentGap++;
                $longestGap = max($longestGap, $currentGap);
            } else {
                $currentGap = 1;
            }
        }

        return $longestGap;
    }

    /**
     * Validasi apakah nomor tengah sesuai dengan pattern group size event
     * Untuk event dengan semua grup ukuran sama (2, 3, 4, dll), pastikan nomor yang dipilih
     * menghasilkan slot yang habis dibagi
     */
    private function isValidMiddleNumberForGroupSize(
        $middleNumber, 
        $totalMembers, 
        $availableStalls,
        $canUpper,
        $canUnder,
        $eventId
    ) {
        // Cek apakah event ini memiliki pattern group size yang konsisten
        $allGroups = ParticipantGroup::where('event_id', $eventId)->get();
        
        if ($allGroups->isEmpty()) {
            return true; // Tidak ada grup lain, allow
        }
        
        // Cek apakah semua grup memiliki ukuran yang sama
        $groupSizes = $allGroups->pluck('total_member')->unique();
        
        // Jika ada variasi ukuran grup (mixed), tidak perlu filter khusus
        if ($groupSizes->count() > 1) {
            return true; // Mixed group sizes, allow any valid number
        }
        
        // Jika semua grup ukuran sama, terapkan filter
        $uniformGroupSize = $groupSizes->first();
        
        // Untuk grup ukuran 1, tidak ada masalah
        if ($uniformGroupSize == 1) {
            return true;
        }
        
        // Untuk grup ukuran 2, 3, 4, dll yang konsisten
        // Pastikan nomor yang dipilih menghasilkan slot yang habis dibagi
        
        // Hitung total slot yang akan terpakai jika pilih nomor ini
        $slotsUsed = [];
        
        if ($canUpper) {
            $upperNumbers = $this->generateUpperNumbers($middleNumber, $totalMembers, $availableStalls);
            if ($upperNumbers) {
                $slotsUsed = array_merge($slotsUsed, $upperNumbers);
            }
        }
        
        if ($canUnder) {
            $underNumbers = $this->generateUnderNumbers($middleNumber, $totalMembers, $availableStalls);
            if ($underNumbers) {
                $slotsUsed = array_merge($slotsUsed, $underNumbers);
            }
        }
        
        // Jika tidak ada slot yang terpakai, invalid
        if (empty($slotsUsed)) {
            return false;
        }
        
        // Untuk grup ukuran genap (2, 4, 6, dll), pastikan jumlah slot genap
        // Untuk grup ukuran 3, pastikan jumlah slot kelipatan 3
        $totalSlotsUsed = count($slotsUsed);
        
        // Check if total slots used is divisible by group size
        if ($totalSlotsUsed % $uniformGroupSize !== 0) {
            return false; // Tidak habis dibagi, akan ada sisa
        }
        
        return true; // Valid
    }


    /**
     * Check if a range of consecutive numbers crosses the layout boundary
     * Layout: Right column (1-111), Left column (112-222)
     * Slots 111 and 112 cannot be consecutive (separated by middle lane)
     */
    private function crossesBoundary($numbers)
    {
        if (empty($numbers) || count($numbers) < 2) {
            return false;
        }
        
        // Check if range includes both 111 (last of right column) and 112 (first of left column)
        $hasRightBoundary = in_array(111, $numbers);
        $hasLeftBoundary = in_array(112, $numbers);
        
        // If both are present, it crosses the boundary
        return $hasRightBoundary && $hasLeftBoundary;
    }

    /**
     * Cek apakah bisa memilih UPPER (ke atas dari nomor tengah)
     */
    private function canSelectUpper($middleNumber, $totalMembers, $availableStalls)
    {
        $upperNumbers = $this->generateUpperNumbers($middleNumber, $totalMembers, $availableStalls);
        
        // Check if valid count
        if (count($upperNumbers) != $totalMembers) {
            return false;
        }
        
        // Check if crosses boundary (111-112)
        if ($this->crossesBoundary($upperNumbers)) {
            return false;
        }
        
        return true;
    }

    /**
     * Cek apakah bisa memilih UNDER (ke bawah dari nomor tengah)
     */
    private function canSelectUnder($middleNumber, $totalMembers, $availableStalls)
    {
        $underNumbers = $this->generateUnderNumbers($middleNumber, $totalMembers, $availableStalls);
        
        // Check if valid count
        if (count($underNumbers) != $totalMembers) {
            return false;
        }
        
        // Check if crosses boundary (111-112)
        if ($this->crossesBoundary($underNumbers)) {
            return false;
        }
        
        return true;
    }

    /**
     * Generate nomor UPPER (naik dari nomor tengah)
     * Nomor tengah IKUT dalam hasil
     */
    private function generateUpperNumbers($middleNumber, $totalMembers, $availableStalls)
    {
        $upperNumbers = [];
        $currentNumber = $middleNumber;
        
        for ($i = 0; $i < $totalMembers; $i++) {
            if (in_array($currentNumber, $availableStalls)) {
                $upperNumbers[] = $currentNumber;
                $currentNumber++; // Naik ke atas setelah menambahkan
            } else {
                // Jika ada nomor yang tidak tersedia, UPPER tidak valid
                break;
            }
        }
        
        return $upperNumbers;
    }

    /**
     * Generate nomor UNDER (turun dari nomor tengah)
     * Nomor tengah IKUT dalam hasil
     */
    private function generateUnderNumbers($middleNumber, $totalMembers, $availableStalls)
    {
        $underNumbers = [];
        $currentNumber = $middleNumber;
        
        for ($i = 0; $i < $totalMembers; $i++) {
            if (in_array($currentNumber, $availableStalls)) {
                $underNumbers[] = $currentNumber;
                $currentNumber--; // Turun ke bawah setelah menambahkan
            } else {
                // Jika ada nomor yang tidak tersedia, UNDER tidak valid
                break;
            }
            
            // Jika sudah mencapai nomor 0, UNDER tidak valid
            if ($currentNumber < 1) {
                break;
            }
        }
        
        return $underNumbers;
    }

    /**
     * Pilih slot scattered (tidak berjejer) secara random
     * Digunakan sebagai fallback ketika tidak ada slot berjejer yang tersedia
     */
    private function selectScatteredSlots($availableStalls, $totalMembers)
    {
        if (count($availableStalls) < $totalMembers) {
            return [];
        }
        
        // Convert ke array biasa dan shuffle
        $available = array_values($availableStalls);
        shuffle($available);
        
        // Ambil sejumlah totalMembers
        $selectedSlots = array_slice($available, 0, $totalMembers);
        
        // Sort agar terlihat rapi di UI
        sort($selectedSlots);
        
        return $selectedSlots;
    }



    public function confirmDraw(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'participantGroupID' => 'required|string',
                'randomStallNumberType' => 'nullable|string',
                'randomStallNumber' => 'required|string',
                'randomStallNumberUpper' => 'nullable|string',
                'randomStallNumberUnder' => 'nullable|string',
            ]);

            if ($validator->fails())
                throw new Error(join(', ', $validator->messages()->all()));
            
            $participantGroup = ParticipantGroup::find($request->participantGroupID);

            if (!$participantGroup)
                throw new Error("Data Grup Tidak Ditemukan");

            if ($participantGroup->raffle_status === ParticipantGroupRaffleStatusEnum::COMPLETED->value)
                throw new Error("Grup Telah Diundi");

            DB::beginTransaction();

            $randomStallNumberConfirmation = null;
            $totalMembers = $participantGroup->total_member;


            // Jika lebih dari 1 anggota, harus pilih UPPER atau UNDER (atau SCATTERED)
            if ($totalMembers > 1) {
                // Validasi bahwa randomStallNumberType harus ada untuk grup > 1
                // Gunakan is_null dan !== '' untuk menghindari masalah dengan nilai '0'
                if (is_null($request->randomStallNumberType) || $request->randomStallNumberType === '') {
                    throw new Error("Silakan pilih ATAS atau BAWAH untuk grup dengan lebih dari 1 anggota");
                }
                
                // Cek apakah ini scattered slots
                if ($request->randomStallNumberType === 'scattered') {
                    // Untuk scattered, langsung ambil dari randomStallNumber
                    $randomStallNumber = json_decode($request->randomStallNumber, true);
                    if (is_array($randomStallNumber) && count($randomStallNumber) > 0) {
                        $randomStallNumberConfirmation = $randomStallNumber;
                    } else {
                        throw new Error("Data scattered slots tidak valid");
                    }
                } else {
                    // Validasi untuk UPPER/UNDER
                    $validator = Validator::make($request->all(), [
                        'randomStallNumberType' => 'required|string|in:' . ConfirmDrawTypeEnum::UPPER->value . ',' . ConfirmDrawTypeEnum::UNDER->value,
                    ]);

                    if ($validator->fails())
                        throw new Error(join(', ', $validator->messages()->all()));

                    $typeDrawConfirm = $request->randomStallNumberType;

                    if ($typeDrawConfirm == ConfirmDrawTypeEnum::UPPER->value) {
                        if (!$request->randomStallNumberUpper) {
                            throw new Error("Data UPPER tidak tersedia");
                        }
                        $randomStallNumberConfirmation = json_decode($request->randomStallNumberUpper, true);
                    } else if ($typeDrawConfirm == ConfirmDrawTypeEnum::UNDER->value) {
                        if (!$request->randomStallNumberUnder) {
                            throw new Error("Data UNDER tidak tersedia");
                        }
                        $randomStallNumberConfirmation = json_decode($request->randomStallNumberUnder, true);
                    } else {
                        throw new Error("Type Draw Confirm Invalid");
                    }
                }


            } else {
                // Jika hanya 1 anggota, langsung ambil dari randomStallNumber
                $randomStallNumber = json_decode($request->randomStallNumber, true);
                if (is_array($randomStallNumber) && count($randomStallNumber) > 0) {
                    $randomStallNumberConfirmation = $randomStallNumber;
                } else {
                    // Fallback jika format lama (string dengan koma)
                    $randomStallNumberConfirmation = explode(',', $request->randomStallNumber);
                }
            }

            // Validasi jumlah nomor sesuai dengan total anggota
            if (count($randomStallNumberConfirmation) != $totalMembers) {
                throw new Error("Jumlah nomor lapak (" . count($randomStallNumberConfirmation) . ") tidak sesuai dengan total anggota (" . $totalMembers . ")");
            }

            // Simpan setiap nomor lapak
            for ($i = 0; $i < count($randomStallNumberConfirmation); $i++) {
                $stallNumber = $randomStallNumberConfirmation[$i];
                
                // Cek apakah lapak sudah dibooking
                $isStallBooked = Participant::where('event_id', $participantGroup->event_id)
                    ->where('stall_number', $stallNumber)
                    ->exists();

                if ($isStallBooked)
                    throw new Error("Lapak " . $stallNumber . " Telah Dibooking");

                // Buat participant baru
                Participant::create([
                    'name' => $participantGroup->name . "-" . ($i + 1),
                    'participant_groups_id' => $participantGroup->id,
                    'event_id' => $participantGroup->event_id,
                    'stall_number' => $stallNumber,
                ]);
            }

            // Update status raffle menjadi completed
            $participantGroup->update([
                "raffle_status" => ParticipantGroupRaffleStatusEnum::COMPLETED->value
            ]);

            DB::commit();

            // Tetap di tab Pengundian setelah konfirmasi undian
            // Tidak redirect ke Layout Lapak
            return redirect()->back()
                ->with('drawnStallNumbers', $randomStallNumberConfirmation) // Nomor lapak yang baru terisi
                ->with('success', 'Undian berhasil dikonfirmasi');
        } catch (\Throwable $th) {
            DB::rollback();

            return back()
                ->with('errors', $th->getMessage());
        }
    }

    public function importParticipantGroups(Request $request, string $eventId)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ], [
            'excel_file.required' => 'File Excel wajib diupload',
            'excel_file.mimes' => 'File harus berformat Excel (.xlsx, .xls, atau .csv)',
            'excel_file.max' => 'Ukuran file maksimal 2MB',
        ]);

        if ($validator->fails()) {
            return back()
                ->with('errors', join(', ', $validator->messages()->all()));
        }

        try {
            $file = $request->file('excel_file');
            
            // Create import instance
            $import = new ParticipantGroupsImport($eventId);
            
            // Import the file
            Excel::import($import, $file);
            
            // Check for errors
            $errors = $import->getErrors();
            $failures = $import->failures();
            
            if (!empty($errors) || count($failures) > 0) {
                // Separate errors by type
                $duplicateNames = [];
                $quotaErrors = [];
                $validationErrors = [];
                
                foreach ($errors as $error) {
                    if (strpos($error, 'sudah digunakan') !== false) {
                        // Extract name from error message
                        preg_match("/Nama '(.+?)' sudah digunakan/", $error, $matches);
                        if (isset($matches[1])) {
                            $duplicateNames[] = $matches[1];
                        }
                    } elseif (strpos($error, 'Kuota tidak cukup') !== false) {
                        $quotaErrors[] = $error;
                    } else {
                        $validationErrors[] = $error;
                    }
                }
                
                // Add validation failures
                foreach ($failures->all() as $failure) {
                    $validationErrors[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }
                
                // Build error message
                $errorMessage = '';
                
                if (!empty($duplicateNames)) {
                    $errorMessage .= "Nama berikut sudah digunakan:\n";
                    foreach ($duplicateNames as $name) {
                        $errorMessage .= "• {$name}\n";
                    }
                    $errorMessage .= "\n";
                }
                
                if (!empty($quotaErrors)) {
                    $errorMessage .= "Masalah kuota:\n";
                    foreach ($quotaErrors as $error) {
                        $errorMessage .= "• {$error}\n";
                    }
                    $errorMessage .= "\n";
                }
                
                if (!empty($validationErrors)) {
                    $errorMessage .= "Error validasi:\n";
                    foreach ($validationErrors as $error) {
                        $errorMessage .= "• {$error}\n";
                    }
                }
                
                return back()
                    ->with('errors', trim($errorMessage));
            }
            
            return redirect()->back()
                ->with('success', 'Data berhasil diimport dari Excel');
                
        } catch (\Throwable $th) {
            return back()
                ->with('errors', 'Gagal import data: ' . $th->getMessage());
        }
    }

    public function downloadTemplateImport()
    {
        return Excel::download(new ParticipantGroupsTemplateExport, 'template_import_peserta.xlsx');
    }
    
    /**
     * Validasi lookahead: Pastikan pilihan ini menyisakan slot berjejer untuk grup lain
     * Simulasi jika kandidat ini dipilih, apakah sisa slot cukup untuk grup yang belum diundi?
     */
    private function validateLookahead($middleNumber, $totalMembers, $availableStalls, $canUpper, $canUnder, $eventId)
    {
        // Dapatkan semua grup yang belum diundi
        $undrawnGroups = ParticipantGroup::where('event_id', $eventId)
            ->where('raffle_status', ParticipantGroupRaffleStatusEnum::NOT_YET->value)
            ->whereIn('status', [ParticipantGroupStatusEnum::DP->value, ParticipantGroupStatusEnum::PAID->value])
            ->get();
        
        // Jika ini grup terakhir, tidak perlu validasi lookahead
        if ($undrawnGroups->count() <= 1) {
            return true; // Lolos validasi
        }
        
        // Simulasi: Pilih UPPER atau UNDER (yang terbaik berdasarkan score)
        $selectedSlots = [];
        if ($canUpper && $canUnder) {
            // Pilih yang menyisakan slot terpanjang
            $upperSlots = $this->generateUpperNumbers($middleNumber, $totalMembers, $availableStalls);
            $underSlots = $this->generateUnderNumbers($middleNumber, $totalMembers, $availableStalls);
            
            $remainingAfterUpper = array_diff($availableStalls, $upperSlots);
            $remainingAfterUnder = array_diff($availableStalls, $underSlots);
            
            $longestAfterUpper = $this->findLongestConsecutiveGap($remainingAfterUpper, Constants::MAX_STALLS);
            $longestAfterUnder = $this->findLongestConsecutiveGap($remainingAfterUnder, Constants::MAX_STALLS);
            
            // Pilih yang menyisakan gap terpanjang
            $selectedSlots = ($longestAfterUpper >= $longestAfterUnder) ? $upperSlots : $underSlots;
        } elseif ($canUpper) {
            $selectedSlots = $this->generateUpperNumbers($middleNumber, $totalMembers, $availableStalls);
        } elseif ($canUnder) {
            $selectedSlots = $this->generateUnderNumbers($middleNumber, $totalMembers, $availableStalls);
        } else {
            return false; // Tidak ada pilihan valid
        }
        
        // Hitung sisa slot setelah pilihan ini
        $remainingSlots = array_diff($availableStalls, $selectedSlots);
        
        // VALIDASI KETAT: Hitung berapa banyak consecutive slots untuk setiap ukuran (2-5)
        // dan bandingkan dengan jumlah grup yang membutuhkan ukuran tersebut
        $groupsToCheck = $undrawnGroups->filter(function($group) use ($totalMembers) {
            // Exclude grup saat ini dari pengecekan
            return $group->total_member != $totalMembers;
        });
        
        // Hitung kebutuhan consecutive slots per ukuran
        $requiredConsecutiveSlots = [];
        for ($size = 2; $size <= 5; $size++) {
            $count = $groupsToCheck->where('total_member', $size)->count();
            if ($count > 0) {
                $requiredConsecutiveSlots[$size] = $count;
            }
        }
        
        // Hitung ketersediaan consecutive slots per ukuran di remaining slots
        $availableConsecutiveSlots = $this->countConsecutiveSlotsBySize($remainingSlots);
        
        // Validasi: Apakah ketersediaan >= kebutuhan untuk setiap ukuran?
        foreach ($requiredConsecutiveSlots as $size => $requiredCount) {
            $availableCount = $availableConsecutiveSlots[$size] ?? 0;
            
            if ($availableCount < $requiredCount) {
                // Tidak cukup consecutive slots untuk ukuran ini
                return false; // Gagal validasi ❌
            }
        }
        
        // VALIDASI TAMBAHAN: Cek apakah ada gap dengan size 1 (slot sendirian)
        // Gap size 1 hanya bisa diisi oleh grup dengan 1 anggota
        // Jika tidak ada grup dengan 1 anggota, maka gap size 1 adalah WASTED SLOT
        $hasGapOfOne = $this->hasGapOfOne($remainingSlots);
        if ($hasGapOfOne) {
            // Cek apakah ada grup dengan 1 anggota yang belum diundi
            $hasGroupWithOneMember = $groupsToCheck->contains(function($group) {
                return $group->total_member == 1;
            });
            
            // Jika tidak ada grup dengan 1 anggota, maka gap size 1 tidak bisa diisi
            if (!$hasGroupWithOneMember) {
                return false; // Gagal validasi: ada gap size 1 yang tidak bisa diisi
            }
        }
        
        return true; // Lolos validasi ✅
    }
    
    /**
     * Cek apakah ada slot berjejer dengan ukuran tertentu
     */
    private function hasConsecutiveSlotsOfSize($availableSlots, $size)
    {
        if (count($availableSlots) < $size) {
            return false;
        }
        
        $available = array_values($availableSlots);
        sort($available);
        
        $consecutiveCount = 1;
        for ($i = 1; $i < count($available); $i++) {
            if ($available[$i] == $available[$i - 1] + 1) {
                $consecutiveCount++;
                
                // Cek boundary crossing (111-112)
                $currentSequence = array_slice($available, $i - $consecutiveCount + 1, $consecutiveCount);
                if ($this->crossesBoundary($currentSequence)) {
                    $consecutiveCount = 1; // Reset jika cross boundary
                    continue;
                }
                
                if ($consecutiveCount >= $size) {
                    return true; // Found consecutive slots
                }
            } else {
                $consecutiveCount = 1; // Reset
            }
        }
        
        return false;
    }
    
    /**
     * Hitung berapa banyak consecutive slots untuk setiap ukuran (2-5)
     * Return: array dengan key = size, value = count
     */
    private function countConsecutiveSlotsBySize($availableSlots)
    {
        if (empty($availableSlots)) {
            return [];
        }
        
        $available = array_values($availableSlots);
        sort($available);
        
        // Cari semua consecutive ranges
        $consecutiveRanges = [];
        $currentRange = [$available[0]];
        
        for ($i = 1; $i < count($available); $i++) {
            if ($available[$i] == $available[$i - 1] + 1) {
                // Cek boundary crossing
                $testRange = array_merge($currentRange, [$available[$i]]);
                if ($this->crossesBoundary($testRange)) {
                    // Save current range dan mulai range baru
                    if (count($currentRange) > 0) {
                        $consecutiveRanges[] = $currentRange;
                    }
                    $currentRange = [$available[$i]];
                } else {
                    $currentRange[] = $available[$i];
                }
            } else {
                // Save current range dan mulai range baru
                if (count($currentRange) > 0) {
                    $consecutiveRanges[] = $currentRange;
                }
                $currentRange = [$available[$i]];
            }
        }
        
        // Save range terakhir
        if (count($currentRange) > 0) {
            $consecutiveRanges[] = $currentRange;
        }
        
        // Hitung berapa banyak slot untuk setiap ukuran (2-5)
        // TIDAK menggunakan greedy allocation - hitung independent untuk setiap size
        // Ini memastikan setiap size punya count yang akurat
        $counts = [
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        ];
        
        foreach ($consecutiveRanges as $range) {
            $rangeLength = count($range);
            
            // Untuk setiap ukuran, hitung berapa banyak grup yang bisa fit
            // INDEPENDENT counting - tidak mengurangi range
            for ($size = 2; $size <= 5; $size++) {
                $canFit = (int)($rangeLength / $size);
                $counts[$size] += $canFit;
            }
        }
        
        return $counts;
    }
    
    /**
     * Cek apakah kandidat ini adalah edge (ujung) dari consecutive range
     * Edge-only selection diterapkan HANYA untuk range EXACTLY 4 dan 6
     * Untuk grup 2: range 4 dan 6
     * Untuk grup 3: range 6
     */
    private function isEdgeCandidate($middleNumber, $availableStalls, $canUpper, $canUnder)
    {
        // Cari consecutive range yang mengandung middleNumber
        $available = array_values($availableStalls);
        sort($available);
        
        // Cari range yang mengandung middleNumber
        $rangeStart = null;
        $rangeEnd = null;
        $inRange = false;
        
        foreach ($available as $i => $num) {
            if ($num == $middleNumber) {
                // Found middleNumber, cari start dan end range
                $rangeStart = $num;
                $rangeEnd = $num;
                
                // Expand ke kiri
                for ($j = $i - 1; $j >= 0; $j--) {
                    if ($available[$j] == $available[$j + 1] - 1) {
                        // Cek boundary
                        $testRange = range($available[$j], $rangeStart);
                        if (!$this->crossesBoundary($testRange)) {
                            $rangeStart = $available[$j];
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }
                
                // Expand ke kanan
                for ($j = $i + 1; $j < count($available); $j++) {
                    if ($available[$j] == $available[$j - 1] + 1) {
                        // Cek boundary
                        $testRange = range($rangeEnd, $available[$j]);
                        if (!$this->crossesBoundary($testRange)) {
                            $rangeEnd = $available[$j];
                        } else {
                            break;
                        }
                    } else {
                        break;
                    }
                }
                
                break;
            }
        }
        
        if ($rangeStart === null || $rangeEnd === null) {
            return true; // Fallback: allow if can't determine range
        }
        
        $rangeLength = $rangeEnd - $rangeStart + 1;
        
        // EDGE-ONLY RULES untuk range EXACTLY 4 dan 6
        // Range lain: Semua kandidat OK
        
        // Jika range == 4, hanya edge yang diperbolehkan
        if ($rangeLength == 4) {
            $isStartEdge = ($middleNumber == $rangeStart && $canUpper);
            $isEndEdge = ($middleNumber == $rangeEnd && $canUnder);
            return $isStartEdge || $isEndEdge;
        }
        
        // Jika range == 6, hanya edge paling ujung yang diperbolehkan
        if ($rangeLength == 6) {
            $isAbsoluteStartEdge = ($middleNumber == $rangeStart && $canUpper);
            $isAbsoluteEndEdge = ($middleNumber == $rangeEnd && $canUnder);
            return $isAbsoluteStartEdge || $isAbsoluteEndEdge;
        }
        
        // Range lain (bukan 4 atau 6): Semua kandidat OK
        return true;
    }
}
