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
                <li class="menu-title" data-key="t-menu">Restaurant Menu</li>

                <li>
                    <a href="{{ route('restaurant.dashboard') }}">
                        <i data-feather="home"></i>
                        <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <li class="menu-title" data-key="t-menu-management">Menu Management</li>

                <li>
                    <a href="{{ route('restaurant.categories.index') }}">
                        <i data-feather="folder"></i>
                        <span data-key="t-categories">Categories</span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('restaurant.products.index') }}">
                        <i data-feather="package"></i>
                        <span data-key="t-products">Products</span>
                    </a>
                </li>

                <li class="menu-title" data-key="t-business">Business</li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="shopping-cart"></i>
                        <span data-key="t-orders">Orders</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ route('restaurant.orders.board') }}">
                                <span data-key="t-board">Orders Board</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('restaurant.orders.index') }}">
                                <span data-key="t-all-orders">All Orders</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <li>
                    <a href="javascript: void(0);" class="has-arrow">
                        <i data-feather="settings"></i>
                        <span data-key="t-settings">Settings</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li>
                            <a href="{{ route('restaurant.settings.edit') }}">
                                <span data-key="t-restaurant-profile">Restaurant Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('restaurant.branches.index') }}">
                                <span data-key="t-branches">Branches</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('restaurant.hours.index') }}">
                                <span data-key="t-hours">Operating Hours</span>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
