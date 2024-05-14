@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">Create Operator</div>
                
                                <div class="card-body">
                                    @if (session('status'))
                                        <div class="alert alert-success" role="alert">
                                            {{ session('status') }}
                                        </div>
                                    @endif
                                    <div  class=" py-2 px-2">
                                        <form action="{{ route('usuario.oper') }}" method="POST">
                                            @csrf
                                            <div class="form-group my-2">
                                                <label for="">User</label>
                                                <input type="text" class="form-control" name="des_usuario" required>
                                            </div>
                                            <div class="form-group my-2">
                                                <label for="">E-mail</label>
                                                <input type="text" class="form-control" name="correo_usuario" required>
                                            </div>
                                            <div class="form-group my-2">
                                                <label for="">Password</label>
                                                <input type="text" class="form-control" name="pass_usuario" required>
                                            </div>
                                            <div class="form-group my-2">
                                                <button type="submit" class="btn btn-primary form-control">Send</button>
                                            </div>
                                            
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
    </div>
</div>
@endsection