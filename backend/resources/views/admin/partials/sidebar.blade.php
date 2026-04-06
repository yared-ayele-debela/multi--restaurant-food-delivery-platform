@php
    $siteSettings = \App\Models\Setting::getInstance();
    $siteName = $siteSettings->site_name ?: 'Food Delivery';
    $logoUrl = $siteSettings->getLogoUrl() ?: asset('admin/dist/assets/images/logo-sm.svg');
@endphp
<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" data-key="t-menu">Menu</li>

                <li>
                    <a href="{{route('admin.dashboard')}}">
                        <i data-feather="home"></i>
                        <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.restaurants.index') }}">
                        <i data-feather="coffee"></i>
                        <span data-key="t-restaurants">Restaurants</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.drivers.index') }}">
                        <i data-feather="truck"></i>
                        <span data-key="t-drivers">Drivers</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.withdrawals.index') }}">
                        <i data-feather="credit-card"></i>
                        <span data-key="t-withdrawals">Withdrawals</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('admin.commissions.index') }}">
                        <i data-feather="dollar-sign"></i>
                        <span data-key="t-commissions">Commission Ledger</span>
                    </a>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="grid"></i>
                        <span data-key="t-apps">
                            Access Control
                        </span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{route('admin.roles.index')}}">
                                <span data-key="t-roles">Roles</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{route('admin.permissions.index')}}">
                                <span data-key="t-permissions">Permissions</span>
                            </a>
                        </li>


                    </ul>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="users"></i>
                        <span data-key="t-forms">Users</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{route('admin.users.index')}}" data-key="t-form-elements">
                                Users
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="{{ route('admin.settings.edit') }}">
                        <i data-feather="settings"></i>
                        <span data-key="t-settings">Settings</span>
                    </a>
                </li>


            </ul>

        </div>
        <!-- Sidebar -->
    </div>
</div>
