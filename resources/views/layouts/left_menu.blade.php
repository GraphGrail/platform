<!-- BEGIN: Left Aside -->
<button class="m-aside-left-close  m-aside-left-close--skin-dark " id="m_aside_left_close_btn">
    <i class="la la-close"></i>
</button>
<div id="m_aside_left" class="m-grid__item  m-aside-left  m-aside-left--skin-dark ">
    <!-- BEGIN: Aside Menu -->
    <div
            id="m_ver_menu"
            class="m-aside-menu  m-aside-menu--skin-dark m-aside-menu--submenu-skin-dark "
            data-menu-vertical="true"
            data-menu-scrollable="false" data-menu-dropdown-timeout="500"
    >
        <ul class="m-menu__nav  m-menu__nav--dropdown-submenu-arrow ">
            <li class="m-menu__item {{ strpos(url()->current(), 'datasets') !== false ? 'm-menu__item--active' : ''}}" aria-haspopup="true" >
                <a  href="{{ route('datasets.index') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-tabs"></i>
                    <span class="m-menu__link-title">
                        <span class="m-menu__link-wrap">
                            <span class="m-menu__link-text">
                                {{ __('Datasets') }}
                            </span>
                        </span>
                    </span>
                </a>
            </li>
            <li class="m-menu__item {{ strpos(url()->current(), 'ai-models') !== false ? 'm-menu__item--active' : ''}}" aria-haspopup="true" >
                <a  href="{{ route('ai-models.index') }}" class="m-menu__link ">
                    <i class="m-menu__link-icon flaticon-network"></i>
                    <span class="m-menu__link-title">
                        <span class="m-menu__link-wrap">
                            <span class="m-menu__link-text">
                                {{ __('Ai Models') }}
                            </span>
                        </span>
                    </span>
                </a>
            </li>
        </ul>
    </div>
    <!-- END: Aside Menu -->
</div>
<!-- END: Left Aside -->
