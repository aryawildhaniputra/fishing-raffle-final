<nav class="navbar bg-body-tertiary position-relative">
    <span class="extra-star star-1"></span>
    <span class="extra-star star-2"></span>
    <span class="extra-star star-3"></span>
    <span class="extra-star star-4"></span>
    <span class="extra-star star-5"></span>
    <span class="extra-star star-6"></span>
    <span class="extra-star star-7"></span>
    <span class="extra-star star-8"></span>
    <div class="container-lg">
        <a href="{{ route('admin.home') }}"
            class="navbar-brand-wrapper d-flex align-items-center gap-3 text-decoration-none">
            <img src="{{ asset('img/logos.png') }}" alt="Logo KP Bintang Jaya Malang" class="navbar-logo" />
            <p class="fw-bold mb-0 text-white text-hero" style="font-size: 24px;">Sistem Undian Pemancingan</p>
        </a>

        @if (auth()->user())
            <div class="dropdown" data-cy="btn-dropdown-account">
                <a class="nav-link d-flex gap-2 pt-3 pt-md-0 align-items-center justify-content-end dropdown-toggle"
                    href="user-edit-profile.html" role="button" aria-current="page" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="{{ asset('img/default-profile.png') }}" class="img-fluid img-avatar" />
                </a>
                <ul class="dropdown-menu dropdown-menu-end px-2">
                    <li class="rounded-2 dropdown-list">
                        <p class="mb-0 text-white text-center">
                            {{ auth()->user()->name }}
                        </p>
                    </li>
                    <li class="rounded-2 dropdown-list my-profile">
                        <a class="dropdown-item text-white rounded-2" href="{{ route('admin.editProfile') }}"
                            data-cy="btn-edit-account"><i class="ri-user-3-line me-2 text-white"></i>Edit Profil</a>
                    </li>
                    <li class="rounded-2 dropdown-list">
                        <button data-cy="btn-logout" type="button"
                            class="dropdown-item btn-submit rounded-2 text-white" data-bs-toggle="modal"
                            data-bs-target="#logoutModal"><i class="ri-logout-circle-line me-2 text-white"></i>Log
                            Out</button>
                    </li>
                </ul>
            </div>
        @else
            <a href="" class="login-link text-decoration-none d-flex align-items-center gap-1 "><i
                    class="ri-login-circle-line"></i>Log In</a>
        @endif
    </div>
</nav>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutModalLabel">Log Out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4 class="text-center">Apakah anda yakin ingin keluar?</h4>
            </div>
            <form action="{{ route('logout') }}" class="form" method="POST">
                @csrf
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button data-cy="btn-logout-confirm" type="submit" class="btn btn-submit btn-danger">Log
                        Out</button>
                </div>
            </form>
        </div>
    </div>
</div>
