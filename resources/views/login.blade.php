@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">Ingresar </div>
                
                                <div class="card-body">
                                    <div  class=" py-2 px-2">
                                        <form action="{{ route('usuario.oper') }}" method="POST">
                                            @csrf
                                            <div class="form-group my-2">
                                                <label for="">Correo</label>
                                                <input type="text" class="form-control" name="correo_usuario" required>
                                            </div>
                                            <div class="form-group my-2">
                                                <label for="">Contraseña</label>
                                                <input type="text" class="form-control" name="pass_usuario" required>
                                            </div>
                                            <div class="form-group my-2">
                                                <button type="submit" class="btn btn-primary form-control">Aceptar</button>
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