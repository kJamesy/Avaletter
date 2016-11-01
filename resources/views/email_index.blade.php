@extends('layouts.app')

@section('view-title', 'Emails - ')

@section('view-header')
    <script>
        window.userEmailsSettings = {
            order_by: '{{ $settings['emails_order_by'] }}',
            order: '{{ $settings['emails_order'] }}',
            paginate: '{{ $settings['emails_paginate'] }}',
        };
        window.emailsLinks = {
            baseUrl: '{{ route('emails.index') }}',
            baseUri: '{{ explode( $_SERVER['SERVER_NAME'], route('emails.index'))[1] }}',
        };
    </script>
    @include('layouts.tinymce')
@endsection

@section('content')
    <div class="container" id="app-emails">
        <div class="row">
            <div class="col-md-2">
                @include('layouts.sidebar')
            </div>
            <div class="col-md-10">
                <emails>

                </emails>
            </div>
        </div>
    </div>
@endsection
