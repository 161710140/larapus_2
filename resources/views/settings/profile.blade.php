@extends('layouts.app') @section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <nav aria-label="breadcrumb primary">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item active" aria-current="page"><a href="{{ url('/home') }}">Home</a> </li>
                    <li class="breadcrumb-item active" aria-current="page">Profil</li>
                    </ol>
            </nav>
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="panel-title">Profil</h2>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="text-muted">Nama</td>
                                <td>{{ auth()->user()->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Email</td>
                                <td>{{ auth()->user()->email }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <a class="btn btn-primary" href="{{ url('/settings/profile/edit') }}">Ubah</a>                </div>
            </div>
        </div>
    </div>
</div>
@endsection