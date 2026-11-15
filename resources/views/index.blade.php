<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Good Neighbor — Mutual aid, close to home</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('requests.index') }}" aria-label="Good Neighbor home">
            <span class="brand-mark" aria-hidden="true">g</span>
            <span>good neighbor</span>
        </a>
        <a class="header-link" href="#how-it-works">How it works <span aria-hidden="true">↗</span></a>
    </header>

    <main>
        <section class="hero">
            <div class="hero-copy">
                <p class="eyebrow"><span class="live-dot"></span> A little help goes a long way</p>
                <h1>Good things happen<br>when neighbors <em>show up.</em></h1>
                <p class="hero-description">Ask for something you need. Offer what you can. Find a helping hand just around the corner.</p>
                <a class="button button-light" href="#requests">Find nearby requests <span aria-hidden="true">↓</span></a>
            </div>
            <div class="hero-art" aria-hidden="true">
                <div class="orbit orbit-one"></div>
                <div class="orbit orbit-two"></div>
                <div class="sun"></div>
                <div class="hill hill-back"></div>
                <div class="hill hill-front"></div>
                <span class="art-spark spark-one">✳</span>
                <span class="art-spark spark-two">✳</span>
                <div class="art-note"><span>neighbors helping</span><strong>neighbors</strong><span class="note-heart">♥</span></div>
            </div>
        </section>

        @if (session('success'))
            <div class="notice notice-success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="notice notice-error" role="alert">
                <strong>We couldn’t submit that yet.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="action-strip" aria-label="Get involved">
            <div class="action-copy">
                <span class="action-icon" aria-hidden="true">＋</span>
                <div><h2>Need a hand?</h2><p>Post a small request. Your neighbors might have just what you need.</p></div>
            </div>
            <button class="button button-green" type="button" data-open-request>Post a request <span aria-hidden="true">↗</span></button>
        </section>

        <section class="requests-section" id="requests">
            <div class="section-heading">
                <div>
                    <p class="eyebrow">The neighborhood board</p>
                    <h2>Small asks. Big heart.</h2>
                </div>
                <span class="request-count">{{ $hasLocation ? $nearbyRequests->count().' open '.Str::plural('request', $nearbyRequests->count()) : 'Your community, nearby' }}</span>
            </div>

            <form class="filter-bar" method="POST" action="{{ route('requests.location') }}" data-location-form>
                @csrf
                <div class="location-copy">
                    <span class="pin-icon" aria-hidden="true">⌖</span>
                    <div><strong>{{ $hasLocation ? 'Showing requests near you' : 'Find help close to home' }}</strong><span>{{ $hasLocation ? 'Your precise location stays private.' : 'Share your location to see what’s needed nearby.' }}</span></div>
                </div>
                <label class="radius-picker">Within
                    <select name="radius" aria-label="Search radius">
                        @foreach ([1, 3, 5, 10] as $option)
                            <option value="{{ $option }}" @selected($radius == $option)>{{ $option }} {{ Str::plural('mile', $option) }}</option>
                        @endforeach
                    </select>
                </label>
                <input type="hidden" name="latitude" data-search-latitude>
                <input type="hidden" name="longitude" data-search-longitude>
                <button class="button button-outline" type="submit" data-locate>Use my location <span aria-hidden="true">→</span></button>
                <p class="form-feedback" data-location-feedback aria-live="polite"></p>
            </form>

            @if ($hasLocation && $nearbyRequests->isNotEmpty())
                <div class="request-grid">
                    @foreach ($nearbyRequests as $req)
                        <article class="request-card">
                            <div class="card-topline">
                                <span class="urgency urgency-{{ $req->urgency }}"><span aria-hidden="true">●</span> {{ ucfirst($req->urgency) }} need</span>
                                <span class="distance">{{ number_format($req->distance, 1) }} mi away</span>
                            </div>
                            <h3>{{ $req->item_name }}</h3>
                            <p class="request-description">{{ $req->description ?: 'A neighbor could use a little help with this.' }}</p>
                            <div class="card-footer">
                                <span>Posted {{ $req->created_at->diffForHumans() }}</span>
                                <span class="open-label"><span></span> Open</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @elseif ($hasLocation)
                <div class="empty-state">
                    <span class="empty-art" aria-hidden="true">✳</span>
                    <h3>It’s quiet around here (for now).</h3>
                    <p>No open requests within {{ $radius }} {{ Str::plural('mile', $radius) }}. Try a wider search or be the first to ask.</p>
                    <button class="text-button" type="button" data-open-request>Post a request <span aria-hidden="true">→</span></button>
                </div>
            @else
                <div class="empty-state location-empty">
                    <span class="empty-art" aria-hidden="true">⌖</span>
                    <h3>Let’s find your people.</h3>
                    <p>Share your location to see open requests in your neighborhood. We keep it private and only show approximate distances.</p>
                    <button class="text-button" type="button" data-trigger-location>Use my location <span aria-hidden="true">→</span></button>
                </div>
            @endif
        </section>

        <section class="how-section" id="how-it-works">
            <p class="eyebrow">Mutual aid, made neighborly</p>
            <div class="how-grid">
                <div><span class="step-number">01</span><h3>Ask for what you need</h3><p>It can be practical, small, and specific. There’s no need to explain more than you want.</p></div>
                <div><span class="step-number">02</span><h3>See what’s close by</h3><p>Browse open requests around you. Exact addresses are never shown on the board.</p></div>
                <div><span class="step-number">03</span><h3>Offer a little help</h3><p>Reach out and work out the details together. Kindness works best neighbor to neighbor.</p></div>
            </div>
        </section>
    </main>

    <footer><a class="brand footer-brand" href="{{ route('requests.index') }}"><span class="brand-mark" aria-hidden="true">g</span><span>good neighbor</span></a><span>Made for neighbors, by neighbors.</span></footer>

    <dialog class="request-dialog" data-request-dialog aria-labelledby="dialog-title">
        <form method="POST" action="{{ route('requests.store') }}" class="request-form" data-request-form>
            @csrf
            <button class="dialog-close" type="button" aria-label="Close" data-close-request>×</button>
            <p class="eyebrow">A small ask is welcome here</p>
            <h2 id="dialog-title">What could you use?</h2>
            <p class="dialog-description">Share only what you’re comfortable sharing. Your request will show an approximate area, not your exact location.</p>
            <label for="item-name">What do you need?</label>
            <input id="item-name" name="item_name" maxlength="120" placeholder="e.g. Warm winter coat, size M" required>
            <label for="item-description">A little more detail <span>(optional)</span></label>
            <textarea id="item-description" name="description" rows="3" maxlength="2000" placeholder="Anything that would help a neighbor say yes."></textarea>
            <label for="urgency">How soon do you need it?</label>
            <select id="urgency" name="urgency" required>
                <option value="low">Whenever possible</option>
                <option value="medium" selected>In the next few days</option>
                <option value="high">Soon</option>
                <option value="immediate">As soon as possible</option>
            </select>
            <input type="hidden" name="latitude" data-request-latitude>
            <input type="hidden" name="longitude" data-request-longitude>
            <p class="form-feedback" data-request-feedback aria-live="polite"></p>
            <button class="button button-green submit-request" type="submit">Share my request <span aria-hidden="true">↗</span></button>
            <p class="privacy-note">Requests expire after 14 days. Your location is rounded to protect your privacy.</p>
        </form>
    </dialog>
</body>
</html>
