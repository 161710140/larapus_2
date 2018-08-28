@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body primary">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    Selamat Datang Di Menu Administrasi Larapus
                    <hr>
                    <h4>Statistik Penulis</h4>
                    <canvas id="chartPenulis" width="400" height="150">
                    </canvas>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="/js/Chart.min.js"></script>
<script>
var data = {
    labels: {!! json_encode($authors) !!},
    datasets: [{
        label: 'Jumlah buku',
        data: {!! json_encode($books) !!},
        backgroundColor: "rgba(151,187,205,0.5)",
        borderColor: "rgba(151,187,205,0.8)",
        }]

        };
        var options = {
        scales: {
        yAxes: [{
        ticks: {
        beginAtZero:true,
        stepSize: 1
        }

        }]

        }
        };

        var ctx = document.getElementById("chartPenulis").getContext("2d");
        var authorChart = new Chart(ctx, {
        type: 'bar',
        data: data,
        options: options
        });
        </script>
@endsection