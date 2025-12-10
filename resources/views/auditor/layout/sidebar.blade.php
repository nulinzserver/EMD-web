 <nav id="sidebar" class="sidebar js-sidebar">
     <div class="sidebar-content js-simplebar">
         <a class='sidebar-brand pb-0'>
             <span class="sidebar-brand-text align-middle">
                 Tender Note
             </span>
             <img src="" class="barnd" alt="">
             <svg class="sidebar-brand-icon align-middle" width="32px" height="32px" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="1.5" stroke-linecap="square"
                 stroke-linejoin="miter" color="#FFFFFF" style="margin-left: -3px">
                 <path d="M12 4L20 8.00004L12 12L4 8.00004L12 4Z"></path>
                 <path d="M20 12L12 16L4 12"></path>
                 <path d="M20 16L12 20L4 16"></path>
             </svg>
         </a>

         <ul class="sidebar-nav">

             <li class="sidebar-item {{ Route::is('dashboard_auditor') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route('dashboard_auditor') }}'>
                     <img src="{{ asset('assets/images/icons/element-4.png') }}" width="20px"> <span class="ms-2 align-middle">Dashboard</span>
                 </a>
             </li>
             <li class="sidebar-item {{ Route::is('user_profile_auditor') ? 'active' : '' }}">
                 <a class='sidebar-link' href='{{ route('user_profile_auditor') }}'>
                     <img src="{{ asset('assets/images/icons/profile.png') }}" width="22px"> <span class="ms-2 align-middle">User name</span>
                 </a>
             </li>
         </ul>
     </div>
 </nav>
