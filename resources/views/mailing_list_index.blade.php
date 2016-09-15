@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                @include('layouts.sidebar')
            </div>
            <div class="col-md-10">
                <mailing-lists>

                </mailing-lists>
            </div>
        </div>
    </div>
@endsection
