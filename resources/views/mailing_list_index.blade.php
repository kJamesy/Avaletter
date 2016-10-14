@extends('layouts.app')

@section('view-header')
    <script>
        window.userMailingListsSettings = {
            order_by: '{{ $settings['mailing_lists_order_by'] }}',
            order: '{{ $settings['mailing_lists_order'] }}',
            paginate: '{{ $settings['mailing_lists_paginate'] }}',
        };
    </script>
@endsection

@section('content')
    <div class="container" id="app-mailing-lists">
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
