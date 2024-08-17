@extends('admin.layouts.layout')
@section('admin_title_content')
    AHVision | Customers List
@endsection
@section('admin_css_content')
  <!-- DataTables -->
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-responsive/css/responsive.bootstrap4.min.css')}}">
  <link rel="stylesheet" href="{{asset('admin/plugins/datatables-buttons/css/buttons.bootstrap4.min.css')}}">
@endsection
@section('admin_content_header')
	<div class="col-sm-6">
		<h1 class="m-0">Customers List</h1>
	</div><!-- /.col -->
	@php 
	  $list = json_encode(['Home', 'Customers List']);
	@endphp
	<x-ad-breadcrumb :list="$list"/>
@endsection

@section('admin_main_content')
	<div class="container-fluid">
        <div class="row">
          	<div class="col-12">
            	

            	<div class="card">
	              	<div class="card-header">
	                	<h3 class="card-title">Website Customers List</h3>
	              	</div>
		            <!-- /.card-header -->
		            <div class="card-body">
		                <table id="example1" class="table table-bordered table-striped">
		                  	<thead>
				                  <tr>
				                    <th>Name</th>
				                    <th>Email</th>
				                    <th>Username</th>
				                    <th>Phone</th>
				                    <th>Status</th>
				                    <th>Actions</th>
				                  </tr>
		                  	</thead>
		                  	<tbody>
		                  		@foreach($data as $user)
				                <tr>
			                    	<td>{{$user->name}}</td>
			                    	<td>{{$user->email}}</td>
			                    	<td>{{$user->username}}</td>
			                    	<td>{{$user->phone}}</td>
			                    	<td>
			                    		<div class="btn-group">
						                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">@if($user->status == 1) Active @else Inactive @endif 
						                    	<span class="sr-only">Toggle Dropdown</span>
						                    </button>
						                    <div class="dropdown-menu" role="menu">
						                    		<form action="{{route('user.profile.status',$user->id)}}" method="post" id="disable-form-{{$user->id}}" style="display: none;">
		                              			@csrf
		                              			@method('put')
		                              			<input type="hidden" name="status" value="@if($user->status == 1) 0 @else 1 @endif">
			                            	</form>
						                      	<a class="dropdown-item" href="#" onclick="
						                            if(confirm('Want to change this product status!'))
						                            {
						                                event.preventDefault();
						                                document.getElementById('disable-form-{{$user->id}}').submit();
						                            }
						                            else
						                            {
						                                event.preventDefault();
						                            }
						                        ">@if($user->status == 1) Inactive @else Active @endif</a>
						                    </div>
						                </div>
			                    	</td>
			                    	<td>
			                    		<div class="btn-group">
						                    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">Options
						                      	<span class="sr-only">Toggle Dropdown</span>
						                    </button>
						                    <div class="dropdown-menu" role="menu">
						                      	<a class="dropdown-item" href="{{route('user.profile.show',$user->id)}}"><i class="fas fa-angle-double-right"></i>View</a>
						                      	<a class="dropdown-item" href="#" onclick="

			                            			if(confirm('Want to Delete this User!'))

						                            {

						                                event.preventDefault();

						                                document.getElementById('delete-form-{{$user->id}}').submit();

						                            }

						                            else

						                            {

						                                event.preventDefault();

						                            }
						                        "><i class="fas fa-angle-double-right"></i>
						                    		{{ __('Delete') }}
						                    	</a>
						                      		<form action="{{route('homecontroller.destroy',$user->id)}}" method="post" id="delete-form-{{$user->id}}" style="display: none;">
						                      			@csrf
											        					@method('delete')
					                            </form>
						                    </div>
						                	</div>
			                    	</td>
				                </tr>
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
	<script src="{{asset('admin/plugins/datatables/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-responsive/js/dataTables.responsive.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-responsive/js/responsive.bootstrap4.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-buttons/js/dataTables.buttons.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.bootstrap4.min.js')}}"></script>
	<script src="{{asset('admin/plugins/jszip/jszip.min.js')}}"></script>
	<script src="{{asset('admin/plugins/pdfmake/pdfmake.min.js')}}"></script>
	<script src="{{asset('admin/plugins/pdfmake/vfs_fonts.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.html5.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.print.min.js')}}"></script>
	<script src="{{asset('admin/plugins/datatables-buttons/js/buttons.colVis.min.js')}}"></script>
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