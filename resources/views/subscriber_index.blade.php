@extends('layouts.app')

@section('content')
<div class="container" id="app-subscribers">
    <div class="row">
        <div class="col-md-2">
            @include('layouts.sidebar')
        </div>
        <div class="col-md-10">
            <subscribers>

            </subscribers>
        </div>
    </div>
</div>
@endsection
