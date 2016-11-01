@extends('layouts.app')

@section('view-title', 'Email Editions - ')

@section('view-header')
    <script>
        window.userEmailEditionsSettings = {
            order_by: '{{ $settings['email_editions_order_by'] }}',
            order: '{{ $settings['email_editions_order'] }}',
            paginate: '{{ $settings['email_editions_paginate'] }}',
        };
        window.emailEditionsLinks = {
            baseUrl: '{{ route('email-editions.index') }}',
            baseUri: '{{ explode( $_SERVER['SERVER_NAME'], route('email-editions.index'))[1] }}',
            emailsBaseUri: '{{ explode( $_SERVER['SERVER_NAME'], route('email-editions.index'))[1] }}'
        }
    </script>
@endsection

@section('content')
    <div class="container" id="app-email-editions">
        <div class="row">
            <div class="col-md-2">
                @include('layouts.sidebar')
            </div>
            <div class="col-md-10">
                <email-editions>

                </email-editions>
            </div>
        </div>
    </div>
@endsection
