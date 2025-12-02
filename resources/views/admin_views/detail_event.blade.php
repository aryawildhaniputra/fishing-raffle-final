@extends('layouts.base')

@section('title', 'Detail Event')

@section('custom_css_link', asset('css/Detail_Event_style/main.css'))

@section('breadcrumbs')
    <div class="breadcrumbs-box mt-1 py-2">
        <div class="page-title mb-1" style="font-size: 24px;">Detail Event</div>
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item align-items-center">
                    <a href="{{ route('admin.home') }}" class="text-decoration-none">Beranda</a>
                </li>
                <li class="breadcrumb-item align-items-center active" aria-current="page">
                    Detail Event
                </li>
            </ol>
        </nav>
    </div>
@endsection

@section('main-content')
    <div class="main-content mt-3">
        <!-- Event Header -->
        <div class="event-header mb-4 p-4 rounded-3"
            style="background: linear-gradient(135deg, #2d6a4f 0%, #40916c 100%); box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="text-white text-center">
                <h2 class="mb-2 fw-bold">{{ $event->name }}</h2>
                <div class="d-flex justify-content-center align-items-center">
                    <i class="ri-calendar-line me-2"></i>
                    <span class="fs-5">{{ $event->event_date_formatted }}</span>
                </div>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card stat-card--forest">
                <h3>Total Lapak</h3>
                <div class="number">{{ App\Support\Constants\Constants::MAX_STALLS }}</div>
            </div>
            <div class="stat-card stat-card--spruce">
                <h3>Lapak Tersedia</h3>
                <div class="number">{{ App\Support\Constants\Constants::MAX_STALLS - $event->total_registrant }}
                </div>
            </div>
            <div class="stat-card stat-card--mint">
                <h3>Total Grup</h3>
                <div class="number">{{ $event->groups_count }}</div>
            </div>
            <div class="stat-card stat-card--teal">
                <h3>Total Pendaftar</h3>
                <div class="number">{{ $event->total_registrant }}</div>
            </div>
        </div>
        <div class="tab-panel">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link text-black active" id="home-tab" data-bs-toggle="tab"
                        data-bs-target="#manage-registrant-tab-pane" type="button" role="tab" data-cy="tab-overview"
                        aria-controls="manage-registrant-tab-pane" aria-selected="true">
                        👥 Kelola Peserta
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="text-black nav-link" id="raffle-tab" data-bs-toggle="tab" data-cy="tab-raffle"
                        data-bs-target="#raffle-tab-pane" type="button" role="tab" aria-controls="raffle-tab-pane"
                        aria-selected="false">
                        🎲 Pengundian
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="text-black nav-link" id="layout-tab" data-bs-toggle="tab" data-cy="tab-layout"
                        data-bs-target="#layout-tab-pane" type="button" role="tab" aria-controls="layout-tab-pane"
                        aria-selected="false">
                        🎣 Layout Lapak
                    </button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane overview-wrapper fade show active bg-white p-2" id="manage-registrant-tab-pane"
                    role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                    <div class="action-wrapper d-lg-flex mt-3 mb-2 justify-content-between align-items-baseline">
                        <div class="wrapper d-flex justify-content-end gap-2">
                            <a href="#" class="btn btn-success">
                                <div data-cy="btn-link-add-type" class="wrapper d-flex gap-2 align-items-center"
                                    id="add" data-bs-toggle="modal" data-bs-target="#addNewModal">
                                    <span class="fw-medium">Tambah Pendaftar</span>
                                </div>
                            </a>
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="exportDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Export
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="exportDropdown">
                                    <li><button class="dropdown-item" id="export-pdf" type="button">PDF</button></li>
                                    <li><button class="dropdown-item" id="export-excel" type="button">Excel</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="wrapper mt-2 mt-lg-0">
                            <div class="input-group">
                                <input data-cy="input-type-name" type="text"
                                    class="form-control py-2 px-3 search-input border" placeholder="Telusuri"
                                    name="type" />
                            </div>
                        </div>
                    </div>
                    <div class="table-wrapper">
                        <table id="participant-group-table" class="bg-white rounded table mt-3 table-hover  rounded-2"
                            style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>No Telepon</th>
                                    <th>Anggota</th>
                                    <th>Status</th>
                                    <th>Status Pengundian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($event->groups as $group)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $group->name }}</td>
                                        <td>{{ $group->phone_num }}</td>
                                        <td>{{ $group->total_member }}</td>
                                        <td>
                                            @switch($group->status)
                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::UNPAID->value)
                                                    <span class="badge bg-danger">Belum Bayar</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::DP->value)
                                                    <span class="badge bg-warning">DP</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::PAID->value)
                                                    <span class="badge bg-primary">Lunas</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($group->raffle_status)
                                                @case(App\Support\Enums\ParticipantGroupRaffleStatusEnum::NOT_YET->value)
                                                    <span class="badge bg-danger">Belum Diundi</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai Diundi</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <div data-bs-toggle="modal" data-bs-target="#editModal"
                                                    class="btn btn-warning btn-edit text-black"
                                                    data-participant-group-id="{{ $group->id }}">
                                                    Edit
                                                </div>
                                                <div class="btn btn-danger btn-delete"
                                                    data-registrant-name="{{ $group->name }}"
                                                    data-raffle-status="{{ $group->raffle_status }}"
                                                    data-delete-link={{ route('admin.destroy.ParticipantGroup', $group->id) }}>
                                                    Hapus
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade document-link-wrapper bg-white p-2" data-cy="wrapper-document-link"
                    id="raffle-tab-pane" role="tabpanel" aria-labelledby="raffle-tab-pane" tabindex="0">
                    @php
                        $count = $groups_not_yet_drawn->count();
                    @endphp
                    @if ($count > 0)
                        <div class="draw-section mb-4 p-4 position-relative overflow-hidden"
                            style="background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 50%, #0d5c3a 100%); 
                                    border-radius: 20px; 
                                    box-shadow: 0 15px 40px rgba(13, 92, 58, 0.4);">

                            {{-- Background Stars --}}
                            <div class="position-absolute w-100 h-100 top-0 start-0"
                                style="opacity: 0.15; pointer-events: none;">
                                <div style="position: absolute; top: 10%; left: 15%; font-size: 20px;">⭐</div>
                                <div style="position: absolute; top: 20%; right: 20%; font-size: 15px;">✨</div>
                                <div style="position: absolute; top: 60%; left: 10%; font-size: 18px;">⭐</div>
                                <div style="position: absolute; bottom: 15%; right: 15%; font-size: 22px;">✨</div>
                                <div style="position: absolute; top: 40%; right: 40%; font-size: 16px;">⭐</div>
                                <div style="position: absolute; bottom: 30%; left: 30%; font-size: 14px;">✨</div>
                                <div style="position: absolute; top: 70%; right: 60%; font-size: 20px;">⭐</div>
                                <div style="position: absolute; top: 25%; left: 50%; font-size: 17px;">✨</div>
                            </div>

                            {{-- Header --}}
                            <div class="text-center mb-4 position-relative">
                                <h2 class="text-white fw-bold mb-2 d-flex align-items-center justify-content-center"
                                    style="font-size: 2rem; letter-spacing: 1.5px;">
                                    <i class="ri-trophy-line me-3" style="font-size: 2rem;"></i>
                                    <span>PENGUNDIAN LAPAK</span>
                                </h2>
                                <p class="text-white mb-0 d-flex align-items-center justify-content-center"
                                    style="opacity: 0.9;">
                                    <i class="ri-information-line me-2"></i>
                                    <span>Sistem akan mengundi peserta berikutnya sesuai urutan</span>
                                </p>
                            </div>

                            {{-- Info Peserta --}}
                            <div class="bg-white rounded-4 p-4 mb-4 position-relative"
                                style="box-shadow: 0 8px 25px rgba(0,0,0,0.15);">
                                <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-3"
                                        style="width: 55px; height: 55px; background: linear-gradient(135deg, #1a7a4f 0%, #0d5c3a 100%);">
                                        <i class="ri-user-line text-white" style="font-size: 26px;"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1 fw-bold" style="color: #0d5c3a;">Peserta Berikutnya</h5>
                                        <small class="text-muted d-flex align-items-center">
                                            <i class="ri-checkbox-circle-line me-1" style="font-size: 14px;"></i>
                                            <span>Siap untuk diundi</span>
                                        </small>
                                    </div>
                                </div>

                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-2 d-flex align-items-center">
                                                <i class="ri-user-3-line me-2"
                                                    style="font-size: 16px; color: #1a7a4f;"></i>
                                                <span>Nama Peserta</span>
                                            </small>
                                            <span class="fw-bold fs-5"
                                                style="color: #0d5c3a;">{{ $groups_not_yet_drawn[$count - 1]['name'] }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-2 d-flex align-items-center">
                                                <i class="ri-phone-line me-2"
                                                    style="font-size: 16px; color: #1a7a4f;"></i>
                                                <span>Nomor Telepon</span>
                                            </small>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold fs-5" id="phoneNumber"
                                                    style="color: #0d5c3a;">{{ $groups_not_yet_drawn[$count - 1]['phone_num'] ? str_repeat('•', strlen($groups_not_yet_drawn[$count - 1]['phone_num'])) : '-' }}</span>
                                                <span class="fw-bold fs-5 d-none" id="phoneNumberReal"
                                                    style="color: #0d5c3a;">{{ $groups_not_yet_drawn[$count - 1]['phone_num'] ?? '-' }}</span>
                                                <button type="button"
                                                    class="btn btn-sm btn-outline-secondary border-0 p-1"
                                                    id="togglePhoneBtn" onclick="togglePhoneNumber()"
                                                    title="Tampilkan/Sembunyikan Nomor" style="min-width: 30px;">
                                                    <i class="ri-eye-line" id="phoneIcon" style="font-size: 18px;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex flex-column">
                                            <small class="text-muted mb-2 d-flex align-items-center">
                                                <i class="ri-group-line me-2"
                                                    style="font-size: 16px; color: #1a7a4f;"></i>
                                                <span>Jumlah Anggota</span>
                                            </small>
                                            <span class="badge d-inline-flex align-items-center"
                                                style="background: linear-gradient(135deg, #1a7a4f 0%, #0d5c3a 100%); 
                                                         font-size: 1rem; 
                                                         padding: 0.6rem 1.2rem; 
                                                         width: fit-content;
                                                         border-radius: 10px;">
                                                <i class="ri-team-line me-2" style="font-size: 1.1rem;"></i>
                                                <span>{{ $groups_not_yet_drawn[$count - 1]['total_member'] }} Orang</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Tombol Undian --}}
                            <div class="text-center position-relative">
                                <button data-participant-group-id="{{ $groups_not_yet_drawn[$count - 1]['id'] }}"
                                    class="btn btn-draw btn-light btn-lg fw-bold shadow-lg d-inline-flex align-items-center"
                                    data-bs-toggle="modal" data-bs-target="#drawModal"
                                    style="padding: 1.1rem 3.5rem; 
                                           font-size: 1.3rem; 
                                           border-radius: 50px; 
                                           transition: all 0.3s ease;
                                           border: 3px solid #fff;">
                                    <i class="ri-dice-line me-3" style="font-size: 1.6rem;"></i>
                                    <span>MULAI UNDIAN</span>
                                </button>
                                <p class="text-white mt-3 mb-0 d-flex align-items-center justify-content-center"
                                    style="opacity: 0.9;">
                                    <i class="ri-arrow-right-circle-line me-2" style="font-size: 18px;"></i>
                                    <span>Klik tombol untuk memulai proses pengundian</span>
                                </p>
                            </div>
                        </div>
                    @else
                        <div class="alert text-center py-5 position-relative overflow-hidden"
                            style="border-radius: 20px; 
                                    border: none; 
                                    background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 50%, #0d5c3a 100%);
                                    box-shadow: 0 15px 40px rgba(13, 92, 58, 0.4);">

                            {{-- Background Stars --}}
                            <div class="position-absolute w-100 h-100 top-0 start-0"
                                style="opacity: 0.15; pointer-events: none;">
                                <div style="position: absolute; top: 15%; left: 20%; font-size: 18px;">⭐</div>
                                <div style="position: absolute; top: 25%; right: 25%; font-size: 16px;">✨</div>
                                <div style="position: absolute; bottom: 20%; left: 15%; font-size: 20px;">⭐</div>
                                <div style="position: absolute; bottom: 25%; right: 20%; font-size: 17px;">✨</div>
                            </div>

                            <div class="position-relative">
                                <i class="ri-checkbox-circle-line text-white" style="font-size: 4.5rem;"></i>
                                <h4 class="text-white fw-bold mt-3 mb-2 d-flex align-items-center justify-content-center">
                                    <i class="ri-check-double-line me-2" style="font-size: 1.8rem;"></i>
                                    <span>Semua Peserta Sudah Diundi</span>
                                </h4>
                                <p class="text-white mb-0 d-flex align-items-center justify-content-center"
                                    style="opacity: 0.9;">
                                    <i class="ri-information-line me-2"></i>
                                    <span>Tidak ada peserta yang perlu diundi saat ini</span>
                                </p>
                            </div>
                        </div>
                    @endif
                    <div class="table-wrapper">
                        <div class="wrapper fs-6 mb-2 fw-semibold">Urutan Pengundian</div>
                        <div class="wrapper mt-2 mt-lg-0 mb-2">
                            <div class="input-group">
                                <input data-cy="input-type-name" type="text"
                                    class="form-control py-2 px-3 second-search-input border" placeholder="Telusuri"
                                    name="type" />
                            </div>
                        </div>
                        <table id="second-participant-group-table"
                            class="bg-white rounded table mt-3 table-hover  rounded-2" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>No Telepon</th>
                                    <th>Anggota</th>
                                    <th>Status</th>
                                    <th>Status Pengundian</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups_not_yet_drawn->reverse()->values() as $group)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $group['name'] }}</td>
                                        <td>{{ $group['phone_num'] }}</td>
                                        <td>{{ $group['total_member'] }}</td>
                                        <td>
                                            @switch($group['status'])
                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::UNPAID->value)
                                                    <span class="badge bg-danger">Belum Bayar
                                                    </span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::DP->value)
                                                    <span class="badge bg-warning">DP</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::PAID->value)
                                                    <span class="badge bg-primary">Lunas
                                                    </span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai Diundi</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($group['raffle_status'])
                                                @case(App\Support\Enums\ParticipantGroupRaffleStatusEnum::NOT_YET->value)
                                                    <span class="badge bg-danger">Belum Diundi</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai Diundi</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <div data-bs-toggle="modal" data-bs-target="#editModal"
                                                    class="btn btn-warning btn-edit text-black"
                                                    data-participant-group-id="{{ $group['id'] }}">
                                                    Edit
                                                </div>
                                                <div class="btn btn-danger btn-delete"
                                                    data-registrant-name="{{ $group['name'] }}"
                                                    data-raffle-status="{{ $group['raffle_status'] }}"
                                                    data-delete-link={{ route('admin.destroy.ParticipantGroup', $group['id']) }}>
                                                    Hapus
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <div class="table-wrapper">
                        <div class="wrapper fs-6 mb-2 fw-semibold">Daftar Yang Sudah Diundi</div>
                        <div class="wrapper mt-2 mt-lg-0 mb-2">
                            <div class="input-group">
                                <input data-cy="input-type-name" type="text"
                                    class="form-control py-2 px-3 third-search-input border" placeholder="Telusuri"
                                    name="type" />
                            </div>
                        </div>
                        <table id="third-participant-group-table"
                            class="bg-white rounded table mt-3 table-hover  rounded-2" style="width: 100%">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Nama</th>
                                    <th>No Telepon</th>
                                    <th>Anggota</th>
                                    <th class="text-center">Nomor Undian</th>
                                    <th>Status</th>
                                    <th>Status Pengundian</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($groups_drawn as $group)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $group['name'] }}</td>
                                        <td>{{ $group['phone_num'] }}</td>
                                        <td>{{ $group['total_member'] }}</td>
                                        <td class="text-center">
                                            @if (isset($group['participants']) && count($group['participants']) > 0)
                                                @php
                                                    $stallNumbers = collect($group['participants'])
                                                        ->pluck('stall_number')
                                                        ->sort()
                                                        ->values();
                                                @endphp
                                                <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                    @foreach ($stallNumbers as $stallNumber)
                                                        <span class="badge bg-success">{{ $stallNumber }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($group['status'])
                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::UNPAID->value)
                                                    <span class="badge bg-danger">Belum Bayar
                                                    </span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::DP->value)
                                                    <span class="badge bg-warning">DP</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::PAID->value)
                                                    <span class="badge bg-primary">Lunas
                                                    </span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai Diundi</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            @switch($group['raffle_status'])
                                                @case(App\Support\Enums\ParticipantGroupRaffleStatusEnum::NOT_YET->value)
                                                    <span class="badge bg-danger">Belum Diundi</span>
                                                @break

                                                @case(App\Support\Enums\ParticipantGroupStatusEnum::COMPLETED->value)
                                                    <span class="badge bg-success">Selesai Diundi</span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <div data-bs-toggle="modal" data-bs-target="#editModal"
                                                    class="btn btn-warning btn-edit text-black"
                                                    data-participant-group-id="{{ $group['id'] }}">
                                                    Edit
                                                </div>
                                                <div class="btn btn-danger btn-delete"
                                                    data-registrant-name="{{ $group['name'] }}"
                                                    data-raffle-status="{{ $group['raffle_status'] }}"
                                                    data-delete-link={{ route('admin.destroy.ParticipantGroup', $group['id']) }}>
                                                    Hapus
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade document-link-wrapper bg-white p-2" data-cy="wrapper-document-link"
                    id="layout-tab-pane" role="tabpanel" aria-labelledby="raffle-tab-pane" tabindex="0">

                    <!-- Legenda Infografis -->
                    <div class="legend-wrapper mb-3 p-3 bg-light rounded border">
                        <div class="d-flex align-items-center mb-2">
                            <i class="ri-information-line me-2" style="font-size: 20px;"></i>
                            <h6 class="fw-bold mb-0">Keterangan:</h6>
                        </div>
                        <div class="d-flex gap-4 flex-wrap">
                            <div class="d-flex align-items-center gap-2">
                                <div class="legend-box bg-warning border border-2 rounded"
                                    style="width: 30px; height: 30px;"></div>
                                <span class="fw-semibold">Tersedia</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <div class="legend-box bg-danger border border-2 rounded"
                                    style="width: 30px; height: 30px;"></div>
                                <span class="fw-semibold">Terisi</span>
                            </div>
                        </div>
                    </div>

                    <div class="layout-wrapper d-lg-flex gap-3">
                        <!-- Nomor Lapak Section dengan Scroll dan Background Kolam Lele -->
                        <div class="layout-wrap col-12 col-lg-8 h-100"
                            style="max-height: 820px; 
                                   overflow-y: auto; 
                                   overflow-x: hidden;
                                    background: linear-gradient(180deg, #5d7a5e 0%, #4a6b4d 30%, #3d5a3e 60%, #2d4a2e 100%);                                   border-radius: 15px;
                                   padding: 20px 10px;
                                   box-shadow: 
                                       inset 0 6px 20px rgba(0,0,0,0.5), 
                                       inset 0 -6px 20px rgba(0,0,0,0.3),
                                       0 4px 15px rgba(0,0,0,0.3);
                                   position: relative;
                                   border: 3px solid #3d5a3e;
                                   display: flex;
                                   flex-direction: column;">

                            <!-- Efek permukaan air dengan ripple -->
                            <div
                                style="position: absolute; top: 0; left: 0; right: 0; height: 60px; 
                                        background: linear-gradient(180deg, 
                                            rgba(255,255,255,0.15) 0%, 
                                            rgba(255,255,255,0.08) 50%, 
                                            transparent 100%);
                                        border-radius: 15px 15px 0 0;
                                        pointer-events: none;
                                        z-index: 1;">
                            </div>

                            <!-- Efek ripple/gelombang air -->
                            <div
                                style="position: absolute; top: 20px; left: 20%; width: 100px; height: 2px;
                                        background: radial-gradient(ellipse, rgba(255,255,255,0.3) 0%, transparent 70%);
                                        border-radius: 50%;
                                        filter: blur(1px);
                                        pointer-events: none;
                                        z-index: 1;">
                            </div>
                            <div
                                style="position: absolute; top: 35px; right: 25%; width: 80px; height: 2px;
                                        background: radial-gradient(ellipse, rgba(255,255,255,0.25) 0%, transparent 70%);
                                        border-radius: 50%;
                                        filter: blur(1px);
                                        pointer-events: none;
                                        z-index: 1;">
                            </div>

                            <!-- Texture air dengan noise pattern -->
                            <div
                                style="position: absolute; top: 0; left: 0; right: 0; bottom: 0;
                                        background-image: 
                                            repeating-linear-gradient(90deg, 
                                                transparent, 
                                                transparent 2px, 
                                                rgba(0,0,0,0.03) 2px, 
                                                rgba(0,0,0,0.03) 4px);
                                        opacity: 0.4;
                                        pointer-events: none;
                                        border-radius: 15px;">
                            </div>

                            <!-- Columns Row -->
                            <div class="d-flex flex-grow-1">
                                <div class="left-column col-1 d-flex gap-1" style="position: relative; z-index: 2;">
                                    {{-- <div class="stall-line col-1 border-bottom bg-dark" style="opacity: 0.3;"></div> --}}
                                    <div class="box-wrapper col-10 d-flex flex-column gap-1">
                                        @foreach ($leftColumnParticipant as $data)
                                            <div @class([
                                                'stall-box',
                                                'ratio ratio-1x1 border border-2 rounded d-flex justify-content-center align-items-center fw-bold text-white',
                                                'bg-danger' => $data['isBooked'],
                                                'bg-warning' => !$data['isBooked'],
                                            ])
                                                data-stall-number="{{ $data['stall_number'] }}"
                                                style="box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                                                {{ $data['stall_number'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="middle-column col-10 px-2" style="position: relative; z-index: 2;">
                                    <!-- Area kosong untuk kolam, background sudah di parent -->
                                </div>

                                <div class="right-column col-1 d-flex gap-1 justify-content-end"
                                    style="position: relative; z-index: 2;">
                                    <div class="box-wrapper col-10 d-flex flex-column gap-1">
                                        @foreach ($rightColumnParticipant as $data)
                                            <div @class([
                                                'stall-box',
                                                'ratio ratio-1x1 border border-2 rounded d-flex justify-content-center align-items-center fw-bold text-white',
                                                'bg-danger' => $data['isBooked'],
                                                'bg-warning' => !$data['isBooked'],
                                            ])
                                                data-stall-number="{{ $data['stall_number'] }}"
                                                style="box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                                                {{ $data['stall_number'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                    {{-- <div class="stall-line col-1 border-bottom bg-dark" style="opacity: 0.3;"></div> --}}
                                </div>
                            </div>

                            {{-- Tempat Siaran Marker - Full Width Row at Bottom --}}
                            <div class="w-100 d-flex justify-content-center mt-3 mb-3"
                                style="position: relative; z-index: 2;">
                                <div class="broadcast-area"
                                    style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
                                            border: 3px solid #ffd700;
                                            border-radius: 12px;
                                            padding: 12px 30px;
                                            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
                                            display: flex;
                                            align-items: center;
                                            gap: 10px;
                                            min-width: 200px;
                                            justify-content: center;">
                                    <i class="ri-live-line" style="font-size: 24px; color: #ffd700;"></i>
                                    <span style="color: white; font-weight: bold; font-size: 16px; letter-spacing: 1px;">
                                        TEMPAT SIARAN
                                    </span>
                                    <i class="ri-broadcast-line" style="font-size: 24px; color: #ffd700;"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Pemancing Section dengan Scroll -->
                        <div class="list-content-wrapper col-12 col-lg-4 p-2">
                            <div class="list-wrapper border border-2 p-1 rounded">
                                <p class="fw-bold fs-4 text-center mb-2">Daftar Pemancing</p>

                                <!-- Search Box -->
                                <div class="mb-2 px-2">
                                    <input type="text" id="searchPemancing" class="form-control form-control-sm"
                                        placeholder="🔍 Cari nama pemancing...">
                                </div>

                                <div class="list-wrap d-flex flex-column gap-1"
                                    style="max-height: 720px; overflow-y: auto;">
                                    @foreach ($participants as $participant)
                                        <div class="box-list col-12 border p-2 fw-bold rounded participant-item"
                                            data-participant-name="{{ strtolower($participant['participant_name']) }}"
                                            data-stall-number="{{ $participant['stall_number'] ?? '' }}"
                                            style="cursor: {{ $participant['stall_number'] ? 'pointer' : 'default' }}; transition: all 0.2s;"
                                            onclick="{{ $participant['stall_number'] ? 'scrollToStall(' . $participant['stall_number'] . ')' : '' }}">
                                            {{ $loop->iteration . '. ' . $participant['participant_name'] }}
                                            @if ($participant['stall_number'])
                                                <span class="badge bg-success ms-2">Lapak
                                                    {{ $participant['stall_number'] }}</span>
                                            @else
                                                <span class="badge bg-secondary ms-2">Belum Diundi</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="addNewModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Tambah Pendaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('admin.store.participant.group') }}" class="form" id="addForm"
                        method="POST">
                        @csrf
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <div class="form-group mb-3">
                            <label for="name" class="mb-1">Nama</label>
                            <input value="" required class="form-control" type="text" name="name"
                                id="name_add" placeholder="Masukkan nama grup atau orang" />
                            <div class="invalid-feedback" id="name-error-add" style="display: none;">
                                Nama sudah digunakan, silakan gunakan nama lain
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="phone_num" class="mb-1">No Telepon (WA)</label>
                            <input value="" class="form-control" type="text" name="phone_num"
                                placeholder="Masukkan no telepon WA" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="total_member" class="mb-1">Jumlah Pemancing</label>
                            <input value="" required class="form-control" type="number" min="1"
                                name="total_member" placeholder="Masukkan jumlah pemancing" />
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Status</label>
                            <select data-cy="input-lecturer" class="form-select" aria-label="Default select example"
                                name="status" required>
                                <option value="">Pilih Status</option>
                                <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::UNPAID }}>Belum Bayar
                                </option>
                                <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::DP }}>DP</option>
                                <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::PAID }}>Lunas</option>
                            </select>
                        </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button data-cy="btn-submit-store" type="submit"
                        class="btn btn-submit btn-success text-white">Simpan</button>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Edit Pendaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="spinner-edit" class="spinner-wrapper d-flex justify-content-center p-2">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="not-found-state-edit">
                    <span class="text-danger">User not found</span>
                </div>
                <div class="content-wrapper-edit d-none" id="content-wrapper-edit">
                    <form action="" class="editForm" id="editForm" method="POST">
                        <div class="modal-body">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="participant_group_id" id="participant-group-id"
                                value="{{ $event->id }}">
                            <div class="form-group mb-3">
                                <label for="name" class="mb-1">Nama</label>
                                <input value="" required class="form-control" type="text" name="name"
                                    id="name_edit" placeholder="Masukkan nama grup atau orang" />
                                <div class="invalid-feedback" id="name-error-edit" style="display: none;">
                                    Nama sudah digunakan, silakan gunakan nama lain
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="phone_num" class="mb-1">No Telepon (WA)</label>
                                <input value="" class="form-control" type="text" name="phone_num"
                                    id="phone_num_edit" placeholder="Masukkan no telepon WA" />
                            </div>
                            <div class="form-group mb-3">
                                <label for="total_member" class="mb-1">Jumlah Pemancing</label>
                                <input value="" required class="form-control" type="number" min="1"
                                    name="total_member" id="total_member_edit" placeholder="Masukkan jumlah pemancing" />
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label">Status</label>
                                <select data-cy="input-lecturer" id="status_edit" class="form-select"
                                    aria-label="Default select example" name="status" required>
                                    <option value="">Pilih Status</option>
                                    <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::UNPAID }}>Belum Bayar
                                    </option>
                                    <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::DP }}>DP</option>
                                    <option value={{ App\Support\Enums\ParticipantGroupStatusEnum::PAID }}>Lunas</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button data-cy="btn-submit-update" type="submit"
                                class="btn btn-warning btn-submit text-black">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Draw Modal -->
    <div class="modal fade" id="drawModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="transform: scale(3); overflow: visible;">
            <div class="modal-content position-relative overflow-hidden"
                style="border: none; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.4);">

                <div class="modal-header position-relative"
                    style="background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); border: none; padding: 20px 30px; z-index: 2;">
                    <h5 class="modal-title text-white fw-bold d-flex align-items-center" style="font-size: 1.4rem;">
                        <i class="ri-trophy-line me-2" style="font-size: 1.6rem; color: #ffd700;"></i>
                        <span>Pengundian Nomor Lapak</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div id="spinner-draw"
                    class="spinner-wrapper d-flex flex-column justify-content-center align-items-center position-relative"
                    style="padding: 60px 40px; z-index: 2;">
                    <div class="spinner-border" role="status"
                        style="width: 4rem; height: 4rem; border-width: 0.4rem; color: #1a7a4f;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 mb-0 fw-semibold" style="color: #0d5c3a;">Memuat data undian...</p>
                </div>

                <div class="not-found-state-draw text-center p-5 d-none position-relative" style="z-index: 2;">
                    <i class="ri-error-warning-line" style="font-size: 64px; color: #dc3545;"></i>
                    <p class="text-danger mt-3 mb-0 fw-semibold fs-5">Terjadi kesalahan saat memuat data</p>
                </div>

                <div class="content-wrapper-draw d-none position-relative" id="content-wrapper-draw" style="z-index: 2;">
                    <form action="{{ route('admin.confirm.draw') }}" method="post" id="form-draw">
                        @csrf
                        <div class="modal-body text-center" style="padding: 30px 40px;">
                            <input type="hidden" name="participantGroupID" id="participant-group-id-form-draw"
                                value="">
                            <input type="hidden" name="randomStallNumberType" id="random-stall-number-type-form-draw"
                                value="">
                            <input type="hidden" name="randomStallNumber" id="random-stall-number-form-draw"
                                value="">
                            <input type="hidden" name="randomStallNumberUpper" id="random-stall-number-upper-form-draw"
                                value="">
                            <input type="hidden" name="randomStallNumberUnder" id="random-stall-number-under-form-draw"
                                value="">

                            {{-- Nama Peserta - Compact --}}
                            @if ($count > 0)
                                <div class="mb-3">
                                    <small class="text-muted d-flex align-items-center justify-content-center">
                                        <i class="ri-user-line me-1" style="font-size: 14px;"></i>
                                        <span>Peserta:</span>
                                    </small>
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <h6 class="mb-0 fw-bold" style="color: #1a7a4f; font-size: 1.1rem;">
                                            {{ $groups_not_yet_drawn[$count - 1]['name'] }}
                                        </h6>
                                        <span class="badge"
                                            style="background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); 
                                                     color: #ffd700; 
                                                     font-size: 0.85rem; 
                                                     padding: 4px 10px;
                                                     border: 1px solid #ffd700;">
                                            <i
                                                class="ri-group-line me-1"></i>{{ $groups_not_yet_drawn[$count - 1]['total_member'] }}
                                            orang
                                        </span>
                                    </div>
                                </div>
                            @endif

                            {{-- Nomor Lapak - FOKUS UTAMA --}}
                            <div class="my-4">
                                <div class="position-relative"
                                    style="background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); 
                                           border-radius: 20px; 
                                           padding: 50px 40px; 
                                           box-shadow: 0 10px 30px rgba(13, 92, 58, 0.3);
                                           border: 4px solid #ffd700;">

                                    <h2 class="random-stall-number purecounter fw-bold position-relative"
                                        id="random-stall-number" data-purecounter-duration="0.3"
                                        data-purecounter-start="0" data-purecounter-end=""
                                        style="font-size: 160px; 
                                               background: linear-gradient(135deg, #ffd700 0%, #ffed4e 50%, #ffd700 100%);
                                               -webkit-background-clip: text;
                                               -webkit-text-fill-color: transparent;
                                               background-clip: text;
                                               margin: 0; 
                                               animation: pulse 1.5s ease-in-out infinite; 
                                               line-height: 1;
                                               filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.6));">
                                    </h2>
                                </div>
                            </div>
                        </div>


                        <div class="modal-footer d-flex flex-column gap-2 position-relative"
                            style="border: none; padding: 20px 30px 30px; background: #f8f9fa; z-index: 2;">

                            <div class="confirm-wrapper-multiple d-flex justify-content-center gap-3 w-100">
                                <button type="button"
                                    class="btn btn-under btn-confirm-draw btn-draw-modal flex-fill d-flex align-items-center justify-content-center"
                                    data-confirm-draw-type="0" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-title="Pilih lapak bagian bawah"
                                    style="border-radius: 12px; 
                                           padding: 14px; 
                                           font-weight: 700; 
                                           font-size: 1.1rem; 
                                           background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); 
                                           border: 3px solid #ffd700; 
                                           transition: all 0.3s ease; 
                                           box-shadow: 0 6px 15px rgba(13, 92, 58, 0.3);
                                           color: white;">
                                    <i class="ri-arrow-down-line"
                                        style="font-size: 1.3rem; line-height: 1; vertical-align: middle; margin-right: 8px;"></i>
                                    <span style="line-height: 1; vertical-align: middle;">BAWAH</span>
                                </button>
                                <button type="button"
                                    class="btn btn-upper btn-confirm-draw btn-draw-modal flex-fill d-flex align-items-center justify-content-center"
                                    data-confirm-draw-type="1" data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-title="Pilih lapak bagian atas"
                                    style="border-radius: 12px; 
                                           padding: 14px; 
                                           font-weight: 700; 
                                           font-size: 1.1rem; 
                                           background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); 
                                           border: 3px solid #ffd700; 
                                           transition: all 0.3s ease; 
                                           box-shadow: 0 6px 15px rgba(13, 92, 58, 0.3);
                                           color: white;">
                                    <i class="ri-arrow-up-line"
                                        style="font-size: 1.3rem; line-height: 1; vertical-align: middle; margin-right: 8px;"></i>
                                    <span style="line-height: 1; vertical-align: middle;">ATAS</span>
                                </button>
                            </div>

                            <div class="confirm-wrapper-single d-flex justify-content-center w-100">
                                <button type="button"
                                    class="btn btn-confirm-draw btn-draw-modal w-100 d-flex align-items-center justify-content-center"
                                    style="border-radius: 12px; 
                                           padding: 14px; 
                                           font-weight: 700; 
                                           font-size: 1.1rem; 
                                           background: linear-gradient(135deg, #0d5c3a 0%, #1a7a4f 100%); 
                                           border: 3px solid #ffd700; 
                                           transition: all 0.3s ease; 
                                           box-shadow: 0 6px 15px rgba(13, 92, 58, 0.3);
                                           color: white;">
                                    <i class="ri-check-line"
                                        style="font-size: 1.3rem; line-height: 1; vertical-align: middle; margin-right: 8px;"></i>
                                    <span style="line-height: 1; vertical-align: middle;">KONFIRMASI</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog ">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="myModalLabel">Hapus Pendaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 class="text-center">Apakah anda yakin menghapus pendaftar <span class="registrant-name"
                            id="registrant-name"></span> ?
                    </h4>
                </div>
                <form action="" class="form" method="post" id="deleteForm">
                    @method('delete')
                    @csrf
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button data-cy="btn-delete-confirm" type="submit" id="deleteType"
                            class="btn btn-submit btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Warning Modal untuk data yang sudah diundi -->
    <div class="modal fade" id="warningDrawnModal" tabindex="-1" aria-labelledby="warningModalLabel"
        aria-hidden="true" style="z-index: 9999;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="warningModalLabel"><i class="ri-alert-line"></i> Peringatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <i class="ri-error-warning-line" style="font-size: 48px; color: #f39c12;"></i>
                    </div>
                    <h5>Data Tidak Dapat Dihapus!</h5>
                    <p class="mb-0">Pendaftar <strong class="drawn-registrant-name"></strong> sudah diundi dan tidak
                        dapat dihapus.</p>
                    <p class="text-muted small">Hanya data yang belum diundi yang dapat dihapus.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom_css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <style>
        /* Hover effect emas untuk tombol Undi Ulang */
        .btn-redraw:hover,
        .btn-redraw:focus,
        .btn-redraw:active,
        .btn-redraw.active {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%) !important;
            border-color: #ffd700 !important;
            color: #0d5c3a !important;
            box-shadow: 0 6px 20px rgba(255, 215, 0, 0.4) !important;
            transform: translateY(-2px) !important;
        }

        /* Override Bootstrap default hover */
        .modal .btn-redraw:hover,
        .modal .btn-redraw:focus {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%) !important;
            border-color: #ffd700 !important;
            color: #0d5c3a !important;
        }

        /* Center alignment untuk semua kolom tabel */
        #participant-group-table thead th,
        #participant-group-table tbody td,
        #second-participant-group-table thead th,
        #second-participant-group-table tbody td,
        #third-participant-group-table thead th,
        #third-participant-group-table tbody td {
            text-align: center !important;
            vertical-align: middle !important;
        }

        /* Batasi lebar kolom Nama dan wrap text */
        #participant-group-table tbody td:nth-child(2),
        #second-participant-group-table tbody td:nth-child(2),
        #third-participant-group-table tbody td:nth-child(2) {
            max-width: 110px;
            word-wrap: break-word;
            white-space: normal;
        }

        /* Batasi lebar kolom Informasi dan wrap text */
        #participant-group-table tbody td:nth-child(8),
        #second-participant-group-table tbody td:nth-child(8),
        #third-participant-group-table tbody td:nth-child(8) {
            max-width: 150px;
            word-wrap: break-word;
            white-space: normal;
        }

        /* CSS untuk Layout Kolam Lele */
        .layout-wrap {
            display: flex;
            align-items: stretch;
            /* Membuat semua kolom sama tinggi */
        }

        .middle-column {
            display: flex;
            align-items: stretch;
        }

        .middle-column .stall-line {
            flex: 1;
            min-height: 100%;
        }

        /* Custom Scrollbar untuk Layout Kolam - Lebih Rapi */
        .layout-wrap::-webkit-scrollbar {
            width: 8px;
        }

        .layout-wrap::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: 10px 0;
        }

        .layout-wrap::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #3d5a3e 0%, #2d4a2e 100%);
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .layout-wrap::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #4a6b4d 0%, #3d5a3e 100%);
        }

        /* Untuk Firefox */
        .layout-wrap {
            scrollbar-width: thin;
            scrollbar-color: #3d5a3e rgba(0, 0, 0, 0.1);
        }

        /* Alternatif: Sembunyikan scrollbar tapi tetap bisa scroll */
        /* Uncomment jika ingin scrollbar tersembunyi */
        /*
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                .layout-wrap::-webkit-scrollbar {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    display: none;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                .layout-wrap {
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    -ms-overflow-style: none;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    scrollbar-width: none;
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                */

        /* Fix untuk semua Bootstrap modal */
        .modal.show {
            display: block !important;
        }

        .modal-dialog {
            position: relative;
            margin: 1.75rem auto;
            pointer-events: auto;
        }

        .modal.show .modal-dialog {
            transform: none !important;
        }

        .modal-content {
            position: relative;
            display: flex;
            flex-direction: column;
            width: 100%;
            pointer-events: auto;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid rgba(0, 0, 0, .2);
            border-radius: 0.3rem;
            outline: 0;
        }

        /* Ensure modal appears above everything */
        #warningDrawnModal {
            z-index: 9999 !important;
        }

        #warningDrawnModal .modal-backdrop {
            z-index: 9998 !important;
        }

        .modal-backdrop.show {
            z-index: 1050 !important;
            opacity: 0.5;
        }

        /* Animation untuk nomor undian */
        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }
        }

        /* Animation untuk highlight kotak lapak yang baru terisi */
        @keyframes highlightPulse {

            0%,
            100% {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
                transform: scale(1);
            }

            50% {
                box-shadow: 0 0 20px 5px rgba(255, 193, 7, 0.8);
                transform: scale(1.1);
            }
        }

        /* Hover effects untuk button modal pengundian */
        .btn-draw-modal:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(45, 106, 79, 0.4) !important;
        }

        /* Highlight stall when clicked from participant list */
        .highlight-stall {
            animation: highlightPulse 1s ease-in-out 3;
            z-index: 100 !important;
        }

        .btn-redraw:hover {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
        }
    </style>
@endpush

@push('js')
    <script src="{{ asset('vendor/purecounterjs-main/dist/purecounter_vanilla.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.flash.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.print.min.js"></script>
    <script>
        $(document).ready(function() {
            // ===== REMEMBER ACTIVE TAB AFTER REFRESH =====
            // Restore tab yang terakhir aktif dari localStorage
            const savedTab = localStorage.getItem('activeEventTab');
            if (savedTab && !{{ session('activeTab') ? 'true' : 'false' }}) {
                const tabTrigger = document.querySelector(`button[data-bs-target="${savedTab}"]`);
                if (tabTrigger) {
                    const tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }

            // Simpan tab yang aktif ke localStorage setiap kali user klik tab
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function(tabButton) {
                tabButton.addEventListener('shown.bs.tab', function(event) {
                    const targetTab = event.target.getAttribute('data-bs-target');
                    localStorage.setItem('activeEventTab', targetTab);
                });
            });

            // Auto scroll ke kotak lapak yang baru terisi (jika user ada di tab Layout Lapak)
            @if (session('drawnStallNumbers'))
                const drawnStallNumbers = @json(session('drawnStallNumbers'));
                console.log('=== AUTO SCROLL DEBUG ===');
                console.log('Drawn Stall Numbers:', drawnStallNumbers);

                // Delay lebih lama untuk memastikan DOM fully rendered
                setTimeout(function() {
                    if (drawnStallNumbers && drawnStallNumbers.length > 0) {
                        // Ambil nomor pertama sebagai target scroll
                        const firstStallNumber = drawnStallNumbers[0];
                        console.log('First stall number to scroll:', firstStallNumber);

                        const targetBox = document.querySelector(
                            `.stall-box[data-stall-number="${firstStallNumber}"]`);

                        if (targetBox) {
                            console.log('Target box found:', targetBox.textContent.trim());

                            // Cari parent scrollable container (.layout-wrap)
                            const scrollContainer = document.querySelector('.layout-wrap');

                            if (scrollContainer) {
                                // Gunakan getBoundingClientRect untuk posisi yang akurat
                                const containerRect = scrollContainer.getBoundingClientRect();
                                const targetRect = targetBox.getBoundingClientRect();

                                // Hitung offset relatif target dari top container
                                const relativeTop = targetRect.top - containerRect.top;
                                const currentScroll = scrollContainer.scrollTop;
                                const containerHeight = scrollContainer.clientHeight;

                                // Formula: scroll sehingga target ada di tengah viewport
                                let scrollPosition = currentScroll + relativeTop - (containerHeight / 2);

                                console.log('Relative top:', relativeTop);
                                console.log('Current scroll:', currentScroll);
                                console.log('Container height:', containerHeight);
                                console.log('Calculated scroll position:', scrollPosition);

                                // Jika scroll position negatif atau terlalu kecil, scroll ke posisi target langsung
                                if (scrollPosition < 100) {
                                    scrollPosition = currentScroll + relativeTop;
                                    console.log('Adjusted scroll position (target too high):',
                                        scrollPosition);
                                }

                                scrollContainer.scrollTo({
                                    top: Math.max(0, scrollPosition),
                                    behavior: 'smooth'
                                });

                                console.log('✅ Scrolled to position:', Math.max(0, scrollPosition));
                            }

                            // Highlight semua kotak yang baru terisi
                            drawnStallNumbers.forEach(function(stallNumber) {
                                const box = document.querySelector(
                                    `.stall-box[data-stall-number="${stallNumber}"]`);
                                if (box) {
                                    box.style.animation = 'highlightPulse 2s ease-in-out 3';
                                    box.style.border = '3px solid #ffc107';

                                    setTimeout(function() {
                                        box.style.animation = '';
                                        box.style.border = '';
                                    }, 6000);
                                }
                            });

                            console.log('✅ Highlight applied');
                        } else {
                            console.error('❌ Target box not found:', firstStallNumber);
                        }
                    }
                }, 1000); // Delay 1000ms untuk DOM fully rendered
            @endif

            const exportFileName =
                'peserta-{{ $event->name }}-{{ \Illuminate\Support\Str::lower(\Carbon\Carbon::parse($event->event_date)->locale('id')->translatedFormat('j F Y')) }}';


            const table = $('#participant-group-table').DataTable({
                order: [
                    [0, 'asc'] // Urutkan kolom No. secara ascending (1, 2, 3, 4, 5...)
                ],
                dom: 'Bfrtip',
                buttons: [{
                        extend: 'pdf',
                        className: 'buttons-pdf-export',
                        filename: exportFileName,
                        title: 'Daftar Peserta\n' + '{{ $event->name }}\n' +
                            '{{ \Carbon\Carbon::parse($event->event_date)->locale('id')->translatedFormat('j F Y') }}',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 6, 7]
                        },
                        customize: function(doc) {
                            // Styling untuk title
                            doc.content[0].text = doc.content[0].text.trim();
                            doc.content[0].alignment = 'center';
                            doc.content[0].fontSize = 16;
                            doc.content[0].bold = true;
                            doc.content[0].margin = [0, 0, 0, 20];

                            // Styling untuk tabel
                            doc.content[1].table.widths = ['5%', '20%', '15%', '10%', '15%', '15%',
                                '20%'
                            ];

                            // Styling untuk header tabel
                            doc.content[1].table.body[0].forEach(function(cell) {
                                cell.fillColor = '#2c3e50';
                                cell.color = '#ffffff';
                                cell.bold = true;
                                cell.alignment = 'center';
                            });

                            // Styling untuk body tabel
                            for (var i = 1; i < doc.content[1].table.body.length; i++) {
                                // Zebra striping
                                if (i % 2 === 0) {
                                    doc.content[1].table.body[i].forEach(function(cell) {
                                        cell.fillColor = '#ecf0f1';
                                    });
                                }

                                // Center alignment untuk kolom tertentu
                                doc.content[1].table.body[i][0].alignment = 'center'; // No
                                doc.content[1].table.body[i][3].alignment = 'center'; // Anggota
                                doc.content[1].table.body[i][4].alignment = 'center'; // Status
                                doc.content[1].table.body[i][5].alignment = 'center'; // Tanggal
                            }

                            // Margin dan layout
                            doc.pageMargins = [40, 60, 40, 60];
                            doc.defaultStyle.fontSize = 10;
                        }
                    },
                    {
                        extend: 'excel',
                        className: 'buttons-excel-export',
                        filename: exportFileName,
                        title: exportFileName,
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 6, 7]
                        }
                    }
                ]
            });

            table.buttons().container().addClass('d-none');

            $('#export-pdf').on('click', function() {
                table.button('.buttons-pdf-export').trigger();
            });

            $('#export-excel').on('click', function() {
                table.button('.buttons-excel-export').trigger();
            });

            $('.search-input').keyup(function() {
                table.search($(this).val()).draw();
            });
        });


        // DataTable untuk tab Pengundian - Urutan Pengundian
        $('#second-participant-group-table').DataTable({
            pageLength: 10,
            order: [
                [0, 'asc']
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('.second-search-input').keyup(function() {
            let table = $('#second-participant-group-table').DataTable();
            table.search($(this).val()).draw();
        });

        // DataTable untuk tab Pengundian - Hasil Pengundian
        $('#third-participant-group-table').DataTable({
            pageLength: 10,
            order: [
                [0, 'desc']
            ],
            language: {
                search: "",
                searchPlaceholder: "Cari...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                infoFiltered: "(difilter dari _MAX_ total data)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            }
        });

        $('.third-search-input').keyup(function() {
            let table = $('#third-participant-group-table').DataTable();
            table.search($(this).val()).draw();
        });


        $(document).on("click", ".btn-edit", function() {
            $(".not-found-state-edit").addClass('d-none');
            $("#content-wrapper-edit").addClass("d-none");
            $("#spinner-edit").removeClass('d-none');

            let participantGroupID = $(this).data("participant-group-id"); // <-- GET ID FROM BUTTON

            let url = "{{ route('admin.get.ParticipantGroupByID', ['id' => ':id']) }}";
            url = url.replace(':id', participantGroupID);

            $.ajax({
                url: url,
                type: "GET",

                success: function(response) {
                    let urlEditForm = "{{ route('admin.update.participant.group', ['id' => ':id']) }}";
                    urlEditForm = url.replace(':id', participantGroupID);
                    $('#editForm').attr('action', urlEditForm);

                    // fill form
                    $("#participant-group-id").val(participantGroupID);
                    $("#name_edit").val(response.data.name);
                    $("#phone_num_edit").val(response.data.phone_num);
                    $("#total_member_edit").val(response.data.total_member);
                    $("#status_edit").val(response.data.status);
                    $("#registration_date_edit").val(response.data.registration_date);
                    $("#information_edit").val(response.data.information);

                    // show form
                    $("#content-wrapper-edit").removeClass("d-none");
                },

                error: function() {
                    $(".not-found-state-edit").removeClass('d-none');
                },

                complete: function() {
                    $("#spinner-edit").addClass('d-none');
                }
            });

        });

        $(document).on("click", ".btn-draw", function() {
            $(".not-found-state-draw").addClass('d-none');
            $("#content-wrapper-draw").addClass("d-none");
            $("#spinner-draw").removeClass('d-none');

            let participantGroupID = $(this).data("participant-group-id"); // <-- GET ID FROM BUTTON

            let url = "{{ route('admin.get.drawStall', ['id' => ':id']) }}";
            url = url.replace(':id', participantGroupID);

            $.ajax({
                url: url,
                type: "GET",

                success: function(response) {
                    // Cek apakah ini scattered slots
                    if (response.data.isScattered) {
                        // Untuk scattered slots, tampilkan semua nomor dengan format khusus
                        let scatteredNumbers = response.data.randomStallNumber.join(', ');
                        $("#random-stall-number").attr('data-purecounter-end', response.data
                            .randomStallNumber[0]);

                        // Tampilkan warning bahwa ini scattered slots
                        $('.modal-body .alert-info, .modal-body .alert-warning').remove();
                        let warningMsg =
                            '<div class="alert alert-warning mt-3 mb-0"><i class="ri-alert-line me-2"></i><strong>Slot Tersebar:</strong> Tidak ada slot berjejer yang tersedia. Nomor lapak yang terpilih: <strong>' +
                            scatteredNumbers + '</strong></div>';
                        $('.modal-body').append(warningMsg);

                        // Set type ke scattered
                        $('#random-stall-number-type-form-draw').val('scattered');

                        // Tampilkan single confirmation button
                        $(".confirm-wrapper-single").removeClass('d-none');
                        $(".confirm-wrapper-multiple").addClass('d-none');
                    } else {
                        // Logic normal untuk consecutive slots
                        $("#random-stall-number").attr('data-purecounter-end', response.data.middle);
                    }

                    new PureCounter({
                        once: false, // Counting at once or recount when the element in view [boolean]
                    });

                    $('#participant-group-id-form-draw').val(response.data.participant_group_id);
                    $('#random-stall-number-form-draw').val(JSON.stringify(response.data
                        .randomStallNumber));

                    // Hanya proses UPPER/UNDER jika bukan scattered
                    if (!response.data.isScattered && response.data.total_member > 1) {
                        $(".confirm-wrapper-multiple").removeClass('d-none');
                        $(".confirm-wrapper-single").addClass('d-none');

                        // Handle UNDER button
                        if (response.data.canUnder && response.data.under) {
                            $('.btn-under').prop('disabled', false);
                            $('.btn-under').removeClass('opacity-50');
                            $('.btn-under').attr('data-bs-title', 'Bawah: ' + response.data.under.join(
                                ', '));
                            $('#random-stall-number-under-form-draw').val(JSON.stringify(response.data
                                .under));
                        } else {
                            $('.btn-under').prop('disabled', true);
                            $('.btn-under').addClass('opacity-50');
                            $('.btn-under').attr('data-bs-title', 'Tidak tersedia (lapak terisi)');
                            $('#random-stall-number-under-form-draw').val('');
                        }

                        // Handle UPPER button
                        if (response.data.canUpper && response.data.upper) {
                            $('.btn-upper').prop('disabled', false);
                            $('.btn-upper').removeClass('opacity-50');
                            $('.btn-upper').attr('data-bs-title', 'Atas: ' + response.data.upper.join(
                                ', '));
                            $('#random-stall-number-upper-form-draw').val(JSON.stringify(response.data
                                .upper));
                        } else {
                            $('.btn-upper').prop('disabled', true);
                            $('.btn-upper').addClass('opacity-50');
                            $('.btn-upper').attr('data-bs-title', 'Tidak tersedia (lapak terisi)');
                            $('#random-stall-number-upper-form-draw').val('');
                        }

                        // Alert jika hanya satu pilihan tersedia
                        // Hapus alert info yang lama terlebih dahulu
                        $('.modal-body .alert-info').remove();

                        if (response.data.canUpper && !response.data.canUnder) {
                            // Tampilkan pesan info
                            let infoMsg =
                                '<div class="alert alert-info mt-3 mb-0"><i class="ri-information-line me-2"></i>Hanya pilihan <strong>ATAS</strong> yang tersedia</div>';
                            $('.modal-body').append(infoMsg);
                        } else if (!response.data.canUpper && response.data.canUnder) {
                            let infoMsg =
                                '<div class="alert alert-info mt-3 mb-0"><i class="ri-information-line me-2"></i>Hanya pilihan <strong>BAWAH</strong> yang tersedia</div>';
                            $('.modal-body').append(infoMsg);
                        }
                    } else if (!response.data.isScattered && response.data.total_member === 1) {
                        $(".confirm-wrapper-single").removeClass('d-none');
                        $(".confirm-wrapper-multiple").addClass('d-none');
                    }

                    // show form
                    $("#content-wrapper-draw").removeClass("d-none");

                    //init tooltip
                    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
                    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap
                        .Tooltip(tooltipTriggerEl))
                },

                error: function(xhr) {
                    $(".not-found-state-draw").removeClass('d-none');
                    // Tampilkan pesan error dari server
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        $(".not-found-state-draw p").text(xhr.responseJSON.message);
                    }
                },

                complete: function() {
                    $("#spinner-draw").addClass('d-none');
                }
            });

        });



        $(document).on('click', '.btn-confirm-draw', function(event) {
            event.preventDefault(); // Prevent default form submission
            event.stopPropagation(); // Stop event bubbling

            let confirmType = $(this).data('confirm-draw-type');

            // Debug logging
            console.log('Button clicked, confirmType:', confirmType);

            // Jika confirmType undefined (untuk scattered atau single member),
            // cek apakah field sudah punya nilai (dari scattered slots logic)
            let currentValue = $('#random-stall-number-type-form-draw').val();

            if (confirmType !== undefined) {
                // Set nilai dari button (untuk UPPER/UNDER)
                $('#random-stall-number-type-form-draw').val(confirmType);
            } else if (!currentValue || currentValue === '') {
                // Jika tidak ada confirmType dan field kosong, set ke null atau empty
                // Ini untuk grup dengan 1 anggota
                $('#random-stall-number-type-form-draw').val('');
            }
            // Jika confirmType undefined tapi field sudah ada nilai (scattered),
            // biarkan nilai yang ada (jangan overwrite)

            // Verify final value
            let setValue = $('#random-stall-number-type-form-draw').val();
            console.log('Field value after setting:', setValue);

            // Disable buttons
            $('.btn-draw-modal').prop('disabled', true);
            document.querySelector(".loading-wrapper").classList.remove('d-none');

            // Submit form after a small delay to ensure value is set
            setTimeout(function() {
                $('#form-draw').submit();
            }, 100);
        });


        $(document).on('click', '.btn-delete', function(event) {
            event.preventDefault();
            event.stopPropagation();

            let name = $(this).data('registrant-name');
            let deleteLink = $(this).data('delete-link');
            let raffleStatus = $(this).data('raffle-status');

            // Set form action
            $('#deleteForm').attr('action', deleteLink);

            // Update modal content based on raffle status
            if (raffleStatus === 'COMPLETED' || raffleStatus === 'completed') {
                // Data sudah diundi - tampilkan peringatan khusus
                $('.modal-body').html(
                    `<h5 class="text-center mb-3">Anda yakin ingin menghapus pendaftar <strong>${name}</strong>?</h5>` +
                    `<div class="alert alert-warning mb-0">` +
                    `<i class="ri-alert-line me-2"></i>` +
                    `<strong>Perhatian:</strong> Data ini sudah diundi. Menghapus data ini akan:<br>` +
                    `<ul class="mb-0 mt-2" style="padding-left: 20px;">` +
                    `<li>Menghapus semua nomor lapak yang sudah diundi</li>` +
                    `<li>Nomor lapak akan tersedia kembali untuk diundi</li>` +
                    `<li>Total peserta event akan berkurang</li>` +
                    `</ul>` +
                    `</div>`
                );
            } else {
                // Data belum diundi - pesan standar
                $('.modal-body').html(
                    `<h4 class="text-center">Apakah anda yakin menghapus pendaftar <span class="registrant-name">${name}</span>?</h4>`
                );
            }

            // Show delete modal
            $('#deleteModal').modal('show');
        });

        // Handle close button untuk warning modal
        $(document).on('click', '#warningDrawnModal .btn-close, #warningDrawnModal [data-bs-dismiss="modal"]', function() {
            const modalEl = document.getElementById('warningDrawnModal');
            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            modalEl.removeAttribute('role');

            const backdrop = document.getElementById('warningModalBackdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
        });

        // Search Pemancing Functionality
        $('#searchPemancing').on('keyup', function() {
            let searchQuery = $(this).val().toLowerCase().trim();

            $('.participant-item').each(function() {
                let participantName = $(this).data('participant-name');

                if (participantName.includes(searchQuery)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        // Hover effect for clickable participants
        $('.participant-item').hover(
            function() {
                if ($(this).data('stall-number')) {
                    $(this).css('background-color', '#e7f3ff');
                }
            },
            function() {
                $(this).css('background-color', '');
            }
        );

        // Scroll to Stall and Highlight Function
        window.scrollToStall = function(stallNumber) {
            // Find the stall box
            let stallBox = $(`.stall-box[data-stall-number="${stallNumber}"]`);

            if (stallBox.length > 0) {
                // Remove previous highlights
                $('.stall-box').removeClass('highlight-stall');

                // Get the layout-wrap container
                let layoutWrap = $('.layout-wrap');

                // Get offset positions
                let containerOffset = layoutWrap.offset().top;
                let stallOffset = stallBox.offset().top;
                let currentScroll = layoutWrap.scrollTop();

                // Calculate the stall's position relative to the container
                let relativePosition = stallOffset - containerOffset + currentScroll;

                // Calculate target scroll position (center the stall in view)
                let containerHeight = layoutWrap.height();
                let stallHeight = stallBox.outerHeight();
                let targetScroll = relativePosition - (containerHeight / 2) + (stallHeight / 2);

                // Ensure scroll doesn't go negative
                targetScroll = Math.max(0, targetScroll);

                console.log('Scrolling to stall:', stallNumber, 'Target scroll:', targetScroll);

                // Smooth scroll
                layoutWrap.animate({
                    scrollTop: targetScroll
                }, 500, function() {
                    // Add highlight class after scroll
                    stallBox.addClass('highlight-stall');

                    // Remove highlight after 3 seconds
                    setTimeout(function() {
                        stallBox.removeClass('highlight-stall');
                    }, 3000);
                });
            } else {
                console.log('Stall not found:', stallNumber);
            }
        };

        // Toggle Phone Number Visibility
        window.togglePhoneNumber = function() {
            const phoneNumber = $('#phoneNumber');
            const phoneNumberReal = $('#phoneNumberReal');
            const phoneIcon = $('#phoneIcon');

            if (phoneNumber.hasClass('d-none')) {
                // Show hidden, hide real
                phoneNumber.removeClass('d-none');
                phoneNumberReal.addClass('d-none');
                phoneIcon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
            } else {
                // Show real, hide hidden
                phoneNumber.addClass('d-none');
                phoneNumberReal.removeClass('d-none');
                phoneIcon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
            }
        };

        // ========================================
        // DUPLICATE NAME VALIDATION
        // ========================================

        // Get all existing participant names
        const existingNames = @json(
            $participantGroups->pluck('name')->map(function ($name) {
                    return strtolower(trim($name));
                })->toArray());

        let originalEditName = ''; // Store original name when editing

        // Validate name for ADD modal
        $('#name_add').on('input', function() {
            const inputName = $(this).val().toLowerCase().trim();
            const errorDiv = document.getElementById('name-error-add');
            const submitBtn = $('#addForm button[type="submit"]');

            // If field is empty or name is unique, hide error
            if (!inputName || !existingNames.includes(inputName)) {
                $(this).removeClass('is-invalid');
                errorDiv.style.display = 'none';
                submitBtn.prop('disabled', false);
            } else {
                // Name already exists
                $(this).addClass('is-invalid');
                errorDiv.style.display = 'block';
                submitBtn.prop('disabled', true);
            }
        });

        // Validate name for EDIT modal
        $('#name_edit').on('input', function() {
            const inputName = $(this).val().toLowerCase().trim();
            const errorDiv = document.getElementById('name-error-edit');
            const submitBtn = $('#editForm button[type="submit"]');

            // If empty, name unchanged, or unique - hide error
            if (!inputName || inputName === originalEditName.toLowerCase().trim() || !existingNames.includes(
                    inputName)) {
                $(this).removeClass('is-invalid');
                errorDiv.style.display = 'none';
                submitBtn.prop('disabled', false);
            } else {
                // Name already exists
                $(this).addClass('is-invalid');
                errorDiv.style.display = 'block';
                submitBtn.prop('disabled', true);
            }
        });

        // Store original name when edit modal opens
        $('#editModal').on('show.bs.modal', function() {
            setTimeout(function() {
                originalEditName = $('#name_edit').val();
            }, 100);
        });

        // Reset validation when modals close
        $('#addNewModal').on('hidden.bs.modal', function() {
            $('#name_add').removeClass('is-invalid');
            document.getElementById('name-error-add').style.display = 'none';
            $('#addForm button[type="submit"]').prop('disabled', false);
        });

        $('#editModal').on('hidden.bs.modal', function() {
            $('#name_edit').removeClass('is-invalid');
            document.getElementById('name-error-edit').style.display = 'none';
            $('#editForm button[type="submit"]').prop('disabled', false);
            originalEditName = '';
        });
    </script>
@endpush
