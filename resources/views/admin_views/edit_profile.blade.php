@extends('layouts.base')

@section('title', 'Edit Profil')

@section('breadcrumbs')
    <div class="breadcrumbs-box mt-1 py-2">
        <div class="page-title mb-1">Edit Profil</div>
        <nav style="--bs-breadcrumb-divider: '>'" aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item align-items-center">
                    <a href="{{ route('admin.home') }}" class="text-decoration-none">Beranda</a>
                </li>
                <li class="breadcrumb-item align-items-center active" aria-current="page">
                    Edit Profil
                </li>
            </ol>
        </nav>
    </div>
@endsection

@section('main-content')
    <div class="main-content mt-3">
        <!-- Alert Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-checkbox-circle-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-error-warning-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Gagal!</strong> {{ session('error') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('info'))
            <div class="alert alert-info alert-dismissible fade show shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ri-information-line me-2" style="font-size: 1.5rem;"></i>
                    <div>
                        {{ session('info') }}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Edit Profile Card -->
        <div class="card border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="card-header text-white py-4"
                style="background: linear-gradient(135deg, #0a2e18 0%, #1fb954 100%); border-bottom: 2px solid rgba(245, 193, 44, 0.4);">
                <h4 class="mb-0 fw-bold">
                    <i class="ri-user-settings-line me-2"></i>
                    Edit Profil
                </h4>
            </div>
            <div class="card-body p-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);">
                <form id="form-tag" action="{{ route('admin.updateProfile', auth()->user()->id) }}" method="POST">
                    @csrf

                    <!-- User Info Section -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3" style="color: #0a2e18;">
                            <i class="ri-user-line me-2"></i>Informasi Pengguna
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Lengkap</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="ri-user-3-line text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0"
                                        placeholder="Masukkan nama lengkap" name="name"
                                        value="{{ old('name', auth()->user()->name) }}" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="ri-at-line text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0"
                                        placeholder="Masukkan username" name="username"
                                        value="{{ old('username', auth()->user()->username) }}" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Password Section -->
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3" style="color: #0a2e18;">
                            <i class="ri-lock-password-line me-2"></i>Ubah Password
                        </h5>
                        <p class="text-muted small mb-3">
                            <i class="ri-information-line me-1"></i>
                            Kosongkan jika tidak ingin mengubah password
                        </p>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-semibold">Password Lama</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="ri-lock-line text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" data-cy="input-old-password"
                                        placeholder="Masukkan password lama" name="old_password" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="ri-lock-unlock-line text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" data-cy="input-new-password"
                                        placeholder="Masukkan password baru" name="new_password" />
                                </div>
                                <small class="text-muted">Minimal 6 karakter</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Ulangi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0">
                                        <i class="ri-shield-check-line text-muted"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0"
                                        data-cy="input-confirm-new-password" placeholder="Ulangi password baru"
                                        name="confirm_password" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('admin.home') }}" class="btn btn-secondary px-4">
                            <i class="ri-close-line me-1"></i>Batal
                        </a>
                        <button type="submit" class="btn text-white px-5 shadow-sm" data-cy="btn-edit-profile-submit"
                            style="background: linear-gradient(135deg, #0a2e18 0%, #1fb954 100%); border: none;">
                            <i class="ri-save-line me-1"></i>Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom styles for edit profile */
        .form-control:focus {
            border-color: #1fb954;
            box-shadow: 0 0 0 0.2rem rgba(31, 185, 84, 0.25);
        }

        .input-group-text {
            border-color: #dee2e6;
        }

        .input-group:focus-within .input-group-text {
            border-color: #1fb954;
            color: #1fb954;
        }

        .input-group:focus-within .form-control {
            border-color: #1fb954;
        }

        .btn:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .card {
            transition: transform 0.3s ease;
        }
    </style>
@endpush
