@extends('layouts.auth')

@section('title', 'Check your email')
@section('heading', 'Check your email')
@section('subheading', "We sent you a link to confirm your address. Click it and you're in.")

@section('content')
    @if (session('status') == 'verification-link-sent')
        <div class="note-pine mb-5">A fresh link is on its way to the email you signed up with.</div>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-pine">Send it again</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link text-sm text-ink-soft">Log out</button>
        </form>
    </div>
@endsection
