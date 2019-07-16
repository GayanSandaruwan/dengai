@extends('layouts.admin-lite')
@section('title')
    Assign Student to Parent
@endsection
@section('page-header')
    Assign Student to Parent
@endsection
@section('optional-header')
@endsection
@section('level')
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Admin</a></li>
        <li class="active">Assign Student to Parent</li>
    </ol>
@endsection
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('studentToParent') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group row">
                                <label for="parent_id" class="col-md-4 col-form-label text-md-right">{{ __('Select Parent') }}</label>

                                <div class="col-md-6">
                                    <select id="parent_id" name="parent_id" class="form-control select2"  data-placeholder="Select a Parent"
                                            style="width: 100%;">
                                        @foreach($parents as $parent)
                                            <option value="{{$parent->id}}">{{$parent->name}} - {{$parent->email}}</option>
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                    <span class="help-block" style="color: red" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="student_id" class="col-md-4 col-form-label text-md-right">{{ __('Select Student') }}</label>

                                <div class="col-md-6">
                                    <select id="student_id" name="student_id" class="form-control select2"  data-placeholder="Select a Student"
                                            style="width: 100%;">
                                        @foreach($students as $student)
                                            <option value="{{$student->id}}">{{$student->name}} - {{$student->email}}</option>
                                        @endforeach
                                    </select>
                                    @error('student_id')
                                    <span class="help-block" style="color: red" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="relationship" class="col-md-4 col-form-label text-md-right">{{ __('Relationship') }}</label>

                                <div class="col-md-6">
                                    <input id="relationship" type="text" class="form-control @error('relationship') is-invalid @enderror" name="relationship" value="{{ old('relationship') }}" required>

                                    @error('relationship')
                                    <span class="help-block" style="color: red" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Assign Student to Parent') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="user-registered-modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title text-center">Successfully Added the Student</h4>
                </div>
                <div class="modal-body">
                    <img src="/images/success.png" class="center-block" style="width: 100px">
                    @isset($success_parent)
                        <h4 class="modal-title text-center">Parent with email id: {{$parent}} successfully Assigned to Students
                            @foreach($success_students as $student)
                                {{$student}},
                            @endforeach
                        </h4>
                    @endisset

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
    <!-- /.modal -->
@endsection
@section('additional-scripts')
    <script type="text/javascript">
        $('.select2').select2();
        @isset($success_parent)
        $('#user-registered-modal').modal('show');
        @endisset
    </script>
@endsection
