<div class="page-sidebar-wrapper">
    <div class="page-sidebar navbar-collapse collapse">

        <ul class="page-sidebar-menu  page-header-fixed" data-keep-expanded="false" data-auto-scroll="true"
            data-slide-speed="200">
            <li class="sidebar-toggler-wrapper hide">
                <div class="sidebar-toggler">
                    <span></span>
                </div>
            </li>

            <li class="nav-item {{ active_menu(['home', '']) }}">
                <a href="{{ url(route('vendor.home')) }}" class="nav-link nav-toggle">
                    <i class="icon-home"></i>
                    <span class="title">{{ __('apps::dashboard.home.title') }}</span>
                    <span class="selected"></span>
                </a>
            </li>
        </ul>

        @if (Module::isEnabled('Order'))
            @if (\Auth::user()->can(['show_orders', 'show_all_orders']))
                <ul class="page-sidebar-menu  page-header-fixed" data-keep-expanded="false" data-auto-scroll="true"
                    data-slide-speed="200">
                    <li
                        class="nav-item  {{ active_slide_menu(['current-orders', 'completed-orders', 'not-completed-orders', 'refunded-orders', 'all-orders']) }}">
                        <a href="javascript:;" class="nav-link nav-toggle">
                            <i class="fa fa-shopping-cart"></i>
                            <span class="title">{{ __('apps::dashboard.aside.tab.orders') }}</span>
                            <span class="arrow open"></span>
                            <span class="selected"></span>
                        </a>
                        <ul class="sub-menu" style="display: block;">

                            @permission('show_orders')
                                <li class="nav-item {{ active_menu('current-orders') }}">
                                    <a href="{{ url(route('vendor.current_orders.index')) }}" class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::dashboard.aside.current_orders') }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ active_menu('completed-orders') }}">
                                    <a href="{{ url(route('vendor.completed_orders.index')) }}" class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::dashboard.aside.completed_orders') }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ active_menu('not-completed-orders') }}">
                                    <a href="{{ url(route('vendor.not_completed_orders.index')) }}"
                                        class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::dashboard.aside.not_completed_orders') }}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{ active_menu('refunded-orders') }}">
                                    <a href="{{ url(route('vendor.refunded_orders.index')) }}" class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::dashboard.aside.refunded_orders') }}</span>
                                    </a>
                                </li>
                            @endpermission

                            @permission('show_all_orders')
                                <li class="nav-item {{ active_menu('all-orders') }}">
                                    <a href="{{ url(route('vendor.all_orders.index')) }}" class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::dashboard.aside.all_orders') }}</span>
                                    </a>
                                </li>
                            @endpermission

                        </ul>
                    </li>
                </ul>
            @endif
        @endif


        @if (Module::isEnabled('Catalog'))
            @if (\Auth::user()->can(['show_products', 'show_addon_categories']))
                <ul class="page-sidebar-menu  page-header-fixed" data-keep-expanded="false" data-auto-scroll="true"
                    data-slide-speed="200">
                    <li class="nav-item  {{ active_slide_menu(['products', 'addon-categories']) }}">
                        <a href="javascript:;" class="nav-link nav-toggle">
                            <i class="fa fa-briefcase"></i>
                            <span class="title">{{ __('apps::dashboard.aside.tab.catalog') }}</span>
                            <span class="arrow open"></span>
                            <span class="selected"></span>
                        </a>
                        <ul class="sub-menu" style="display: block;">

                            @permission('show_products')
                                <li class="nav-item {{ active_menu('products') }}">
                                    <a href="{{ url(route('vendor.products.index')) }}" class="nav-link nav-toggle">
                                        {{-- <i class="icon-settings"></i> --}}
                                        <span class="title">{{ __('apps::vendor.aside.products') }}</span>
                                    </a>
                                </li>
                            @endpermission

                            @if (config('setting.products.toggle_addons') == 1 &&
                                auth()->user()->can('show_product_addons'))
                                @permission('show_addon_categories')
                                    <li class="nav-item {{ active_menu('addon-categories') }}">
                                        <a href="{{ url(route('vendor.addon_categories.index')) }}"
                                            class="nav-link nav-toggle">
                                            {{-- <i class="icon-briefcase"></i> --}}
                                            <span class="title">{{ __('apps::dashboard.aside.addon_categories') }}</span>
                                        </a>
                                    </li>
                                @endpermission

                                @permission('show_addon_options')
                                    <li class="nav-item {{ active_menu('addon-options') }}">
                                        <a href="{{ url(route('vendor.addon_options.index')) }}"
                                            class="nav-link nav-toggle">
                                            {{-- <i class="icon-briefcase"></i> --}}
                                            <span class="title">{{ __('apps::dashboard.aside.addon_options') }}</span>
                                        </a>
                                    </li>
                                @endpermission
                            @endif

                        </ul>
                    </li>
                </ul>
            @endif
        @endif

    </div>
</div>
