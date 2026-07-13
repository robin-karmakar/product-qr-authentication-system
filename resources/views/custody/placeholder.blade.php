@extends('layouts.app')

@section('title', 'Custody Dashboard')

@section('content')
    <div class="text-center py-5">
        <h4>Custody Dashboard</h4>
        <p class="text-muted">The scan-to-confirm supply chain custody flow is built in Module 7.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-sm">Log Out</button>
        </form>
    </div>
@endsection
