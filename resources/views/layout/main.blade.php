@include('layout.header')
@include('layout.sidebar')
<!-- Loading Spinner -->
<div id="loading" style="display:none; flex-direction: column; align-items: center; justify-content: center; position: fixed; top:0; left:0; right:0; bottom:0; background:rgba(255,255,255,0.8); z-index:9999;">
    <div class="spinner"></div>
    <p id="loading-text">Loading...</p>
    <button id="refresh-btn" style="display:none; margin-top:10px; padding:8px 15px; border:none; background:#007bff; color:#fff; border-radius:5px; cursor:pointer;">
        Refresh Page
    </button>
</div>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <br>
        <!-- Small boxes (Stat box) -->
        @yield('content')
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>

@include('layout.footer')