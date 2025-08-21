@include('layout.header')
@include('layout.sidebar')
<!-- Loading Spinner -->
<div id="loading" style="display: none;">
    <div class="spinner"></div>
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