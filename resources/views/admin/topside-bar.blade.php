<!-- ============================================================== -->
        <!-- navbar -->
        <!-- ============================================================== -->
        <div class="dashboard-header">
            <nav class="navbar navbar-expand-lg bg-white fixed-top">
                <a class="navbar-brand" href="{{ route('admin.dashboard') }}">Bill Vault</a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse " id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto navbar-right-top">
                        <li class="nav-item">
                            <div id="custom-search" class="top-search-bar">
                                <input class="form-control" type="text" placeholder="Search..">
                            </div>
                        </li>
                        <li class="nav-item dropdown nav-user">
                            {{-- <i class="fa fa-fw fa-power"></i> --}}
                            <a class="nav-link nav-user-img" href="#" id="navbarDropdownMenuLink2" 
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-power-off"></i>
                            </a>
                            <form method="POST" id="logout-form" action="{{ route('admin.logout') }}">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        <!-- ============================================================== -->
        <!-- end navbar -->
        <!-- ============================================================== -->
        <!-- ============================================================== -->
        <!-- left sidebar -->
        <!-- ============================================================== -->
        <div class="nav-left-sidebar sidebar-dark">
            <div class="menu-list">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <a class="d-xl-none d-lg-none" href="#">Dashboard</a>
                    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav flex-column">
                            <li class="nav-divider">
                                Menu
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.dashboard']) ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fa fa-fw fa-user-circle"></i>Dashboard</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-2" aria-controls="submenu-2"><i class="fa fa-fw fa-users"></i>Customer</a>
                                <div id="submenu-2" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        {{-- <li class="nav-item">
                                            <a class="nav-link" href="pages/cards.html">Add Customer</a>
                                        </li> --}}
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.customer.list') }}">Manage Customers</a>
                                        </li>
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.customer.topup') }}">Top Up</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#kyc" aria-controls="kyc"><i class="fa fa-fw fa-users"></i>Manage KYC</a>
                                <div id="kyc" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.manage-kyc') }}">Manage Level Limites</a>
                                        </li> 
                                        <!--<li class="nav-item">-->
                                        <!--    <a class="nav-link" href="{{ route('admin.customer.list') }}">First Level Requests</a>-->
                                        <!--</li>-->
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.kyc.level-two') }}">Second Level Requests</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.kyc.level-three') }}">Third Level Requests</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="{{ route('admin.kyc.manual-level-two-kyc') }}">Manual KYC</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-3" aria-controls="submenu-2"><i class="fa fa-fw fa-users"></i>Airtime To Cash</a>
                                <div id="submenu-3" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" href="/admin/airtime-cash">Settings</a>
                                        </li>
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" href="/admin/airtime-cash-transactions">Transactions History</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-4" aria-controls="submenu-2"><i class="fa fa-fw fa-users"></i>Sell Gift Card</a>
                                <div id="submenu-4" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" href="/admin/gift-cards">Manage Available Cards</a>
                                        </li>
                                        
                                        <li class="nav-item">
                                            <a class="nav-link" href="/admin/gift-card-transactions">Transactions History</a>
                                        </li>
                                    </ul>
                                </div>
                            </li> -->
                            
                            
                            
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.transaction-settings']) ? 'active' : '' }}" href="{{ route('admin.transaction-settings') }}"><i class="fab fa-fw fa-product-hunt"></i>Transaction Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.banner-settings']) ? 'active' : '' }}" href="{{ route('admin.banner-settings') }}">
                                    <i class="fab fa-fw fa-product-hunt"></i>Banner Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.utility-settings']) ? 'active' : '' }}" href="{{ route('admin.utility-settings') }}"><i class="fab fa-fw fa-product-hunt"></i>Utility Settings</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.referral-settings']) ? 'active' : '' }}" href="{{ route('admin.referral-settings') }}"><i class="fa fa-fw fa-envelope"></i>Referral Settings</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.smtp-settings']) ? 'active' : '' }}" href="{{ route('admin.smtp-settings') }}"><i class="fa fa-fw fa-envelope"></i>Smtp Settings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.thirdparty-settings']) ? 'active' : '' }}" href="{{ route('admin.thirdparty-settings') }}"><i class="fab fa-fw fa-product-hunt"></i>Third Party</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.broadcast-settings']) ? 'active' : '' }}" href="{{ route('admin.broadcast-settings') }}"><i class="fab fa-fw fa-product-hunt"></i>Broadcast Message</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.notification']) ? 'active' : '' }}" href="{{ route('admin.notification') }}"><i class="fa fa-fw fa-rectangle-list"></i>Push Notification</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.change-password']) ? 'active' : '' }}" href="{{ route('admin.change-password') }}">
                                    <i class="fa fa-fw fa-rectangle-list"></i>Change Password</a>
                            </li>
                            
                            <li class="nav-item">
                                <a class="nav-link {{ in_array(Route::currentRouteName(), ['admin.login-history']) ? 'active' : '' }}" href="{{ route('admin.login-history') }}">
                                    <i class="fa fa-fw fa-rectangle-list"></i>Login History
                                </a>
                            </li>
                            
                            <li class="nav-item ">
                            <a class="nav-link nav-user-img" href="#" id="navbarDropdownMenuLink2" 
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-power-off"></i> Logout
                            </a>
                            <form method="POST" id="logout-form" action="{{ route('admin.logout') }}">
                                @csrf
                            </form>
                        </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-3" aria-controls="submenu-3"><i class="fas fa-fw fa-chart-pie"></i>Chart</a>
                                <div id="submenu-3" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-c3.html">C3 Charts</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-chartist.html">Chartist Charts</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-charts.html">Chart</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-morris.html">Morris</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-sparkline.html">Sparkline</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/chart-gauge.html">Guage</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item ">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-4" aria-controls="submenu-4"><i class="fab fa-fw fa-wpforms"></i>Forms</a>
                                <div id="submenu-4" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/form-elements.html">Form Elements</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/form-validation.html">Parsely Validations</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/multiselect.html">Multiselect</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/datepicker.html">Date Picker</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/bootstrap-select.html">Bootstrap Select</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-5" aria-controls="submenu-5"><i class="fas fa-fw fa-table"></i>Tables</a>
                                <div id="submenu-5" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/general-table.html">General Tables</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/data-tables.html">Data Tables</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-divider">
                                Features
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-6" aria-controls="submenu-6"><i class="fas fa-fw fa-file"></i> Pages </a>
                                <div id="submenu-6" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/blank-page.html">Blank Page</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/blank-page-header.html">Blank Page Header</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/login.html">Login</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/404-page.html">404 page</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/sign-up.html">Sign up Page</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/forgot-password.html">Forgot Password</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/pricing.html">Pricing Tables</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/timeline.html">Timeline</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/calendar.html">Calendar</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/sortable-nestable-lists.html">Sortable/Nestable List</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/widgets.html">Widgets</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/media-object.html">Media Objects</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/cropper-image.html">Cropper</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/color-picker.html">Color Picker</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-7" aria-controls="submenu-7"><i class="fas fa-fw fa-inbox"></i>Apps <span class="badge badge-secondary">New</span></a>
                                <div id="submenu-7" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/inbox.html">Inbox</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/email-details.html">Email Detail</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/email-compose.html">Email Compose</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/message-chat.html">Message Chat</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-8" aria-controls="submenu-8"><i class="fas fa-fw fa-columns"></i>Icons</a>
                                <div id="submenu-8" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-fontawesome.html">FontAwesome Icons</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-material.html">Material Icons</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-simple-lineicon.html">Simpleline Icon</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-themify.html">Themify Icon</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-flag.html">Flag Icons</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/icon-weather.html">Weather Icon</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-9" aria-controls="submenu-9"><i class="fas fa-fw fa-map-marker-alt"></i>Maps</a>
                                <div id="submenu-9" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/map-google.html">Google Maps</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="pages/map-vector.html">Vector Maps</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-10" aria-controls="submenu-10"><i class="fas fa-f fa-folder"></i>Menu Level</a>
                                <div id="submenu-10" class="collapse submenu" style="">
                                    <ul class="nav flex-column">
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Level 1</a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#" data-toggle="collapse" aria-expanded="false" data-target="#submenu-11" aria-controls="submenu-11">Level 2</a>
                                            <div id="submenu-11" class="collapse submenu" style="">
                                                <ul class="nav flex-column">
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#">Level 1</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" href="#">Level 2</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" href="#">Level 3</a>
                                        </li>
                                    </ul>
                                </div>
                            </li> --}}
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
        <!-- ============================================================== -->
        <!-- end left sidebar -->
        <!-- ============================================================== -->