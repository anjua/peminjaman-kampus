@extends('layouts.app')

@section('title', 'Kalender Ruang')

@push('page-css')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/select2/select2.css') }}" />
@endpush

@section('content')
<div class="card">
    <div class="card-body border-bottom d-flex flex-wrap gap-3 align-items-end">
        <div style="min-width: 260px">
            <label class="form-label" for="filter-ruang">Ruang</label>
            <select id="filter-ruang" class="select2 form-select" data-placeholder="Semua ruang">
                <option value=""></option>
                @foreach ($ruang as $r)
                <option value="{{ $r->id }}">{{ $r->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex gap-3">
            @foreach ($status as $s)
            <span class="d-flex align-items-center gap-1"><span class="badge badge-dot bg-{{ $s->warna }}"></span>{{
                $s->label }}</span>
            @endforeach
        </div>
    </div>
    <div class="card-body">
        <div id="kalender" data-url="{{ route('kalender.acara') }}"></div>
    </div>
</div>
@endsection

@push('vendor-js')
<script src="{{ asset('assets/vendor/libs/fullcalendar/fullcalendar.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/select2/select2.js') }}"></script>
@endpush

@push('page-js')
<script src="{{ asset('js/kalender.js') }}"></script>
@endpush