@extends('layouts.app')

@section('view-title', 'Email Templates - ')

@section('view-header')
    <script>
        window.userEmailTemplatesSettings = {
            order_by: '{{ $settings['templates_order_by'] }}',
            order: '{{ $settings['templates_order'] }}',
            paginate: '{{ $settings['templates_paginate'] }}',
        };
        window.emailTemplatesLinks = {
            baseUrl: '{{ route('email-templates.index') }}',
            baseUri: '{{ explode( $_SERVER['SERVER_NAME'], route('email-templates.index'))[1] }}',
        };
    </script>
    @include('layouts.tinymce')
@endsection

@section('content')
    <div class="container" id="app-email-templates">
        <div class="row">
            <div class="col-md-2">
                @include('layouts.sidebar')
            </div>
            <div class="col-md-10">
                {{--<form method="post">--}}
                    {{--<textarea id="tinymce">Hello, World!</textarea>--}}
                {{--</form>--}}
                <email-templates>

                </email-templates>
            </div>
        </div>
    </div>
@endsection
