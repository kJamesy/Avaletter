@extends('layouts.app')

@section('view-title', 'Subscribers - ')

@section('view-header')
    <script>
        window.userSubscribersSettings = {
            order_by: '{{ $settings['subscribers_order_by'] }}',
            order: '{{ $settings['subscribers_order'] }}',
            paginate: '{{ $settings['subscribers_paginate'] }}',
        };
        window.subscribersLinks = {
            baseUrl: '{{ route('subscribers.index') }}',
            baseUri: '{{ explode( $_SERVER['SERVER_NAME'], route('subscribers.index'))[1] }}',
        }
    </script>
@endsection

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
