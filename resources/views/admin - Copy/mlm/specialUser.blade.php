@extends('admin.layouts.layout')
@section('admin_title_content')
    AHVision | Mlm Users
@endsection
@section('admin_css_content')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{Vite::asset('resources/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{Vite::asset('resources/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{Vite::asset('resources/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
@endsection
@section('admin_content_header')
	<div class="col-sm-6">
		<h1 class="m-0">Special MLM Users</h1>
	</div><!-- /.col -->
	@php 
	  $list = json_encode(['Home', 'MLM users']);
	@endphp
	<x-ad-breadcrumb :list="$list"/>
@endsection

@section('admin_main_content')
	<div class="container-fluid">
        <div class="row">
          	<div class="col-12">
            	

            	<div class="card">
	              	<div class="card-header">
	                	<h3 class="card-title">Special Users List</h3>
	              	</div>
		            <!-- /.card-header -->
		            <div class="card-body">
		                <table id="example1" class="table table-bordered table-striped">
		                  	<thead>
				                  <tr>
				                  	<th>No</th>
				                    <th>Name</th>
				                    <th>Email</th>
				                    <th>Phone</th>
				                    <th>User Type</th>
				                    <th>Status</th>
				                    <th>Payment Status</th>
				                    <th>Actions</th>
				                  </tr>
		                  	</thead>
		                  	<tbody>
		                  		@foreach($data as $item)
				                	@if($item->user->status == 1 && isset($item->refferer_id) && empty($item->parent_id))
					                <tr>
					                		<td>{{$loop->index +1 }}</td>
				                    	<td>{{$item->user->name}}</td>
				                    	<td>{{$item->user->email}}</td>
				                    	<td>{{$item->user->phone}}</td>
				                    	<td>
				                    		@if(isset($item->refferer_id) && empty($item->parent_id))
				                    			Special User
				                    		@else
				                    			{{!isset($item->parent_id) ? 'Root User' : 'Child User'}}
				                    		@endif
				                    	</td>	
				                    	<td>
	                            	<label class="badge badge-success">Active</label>
				                    	</td>
				                    	<td>
		                    				<label class="badge badge-primary">Paid</label>
					                    		
				                    	</td>
				                    	<td>
					                    		<div class="btn-group">
									                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Options
									                      	<span class="sr-only">Toggle Dropdown</span>
									                    </button>
									                    <div class="dropdown-menu" role="menu">
									                      	<a class="dropdown-item" href="{{route('adminmlm.show',$item->id)}}"><i class="fas fa-angle-double-right"></i>View</a>
									                      	<a class="dropdown-item" href="{{route('userchat.show',$item->id)}}"><i class="fas fa-angle-double-right"></i>Chat</a>
									                      	
									                    </div>
								                	</div>
				                    	</td>
					                </tr>
					                @endif
				                @endforeach
		                  	</tbody>
		                </table>
		            </div>
		            <!-- /.card-body -->
            	</div>
            	<!-- /.card -->
          	</div>
          	<!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
@endsection

@section('admin_js_content')
	<!-- DataTables  & Plugins -->
	<script src="{{Vite::asset('resources/plugins/datatables/jquery.dataTables.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/jszip/jszip.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/pdfmake/pdfmake.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/pdfmake/vfs_fonts.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
	<script src="{{Vite::asset('resources/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
	<!-- Page specific script -->
	<script>
		$(function () {
		    $("#example1").DataTable({
		      	"responsive": true, "lengthChange": false, "autoWidth": false,
		      	"buttons": ["copy", "csv", "excel", "pdf", "print", "colvis"]
		    }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
		    
		});
	</script>
@endsection