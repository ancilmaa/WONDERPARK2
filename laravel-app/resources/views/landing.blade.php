@php
    $wpColors = [
        'coral'  => ['solid' => 'var(--coral)',       'bg' => 'rgba(255,90,95,.16)',  'text' => 'var(--coral-dark)'],
        'teal'   => ['solid' => 'var(--teal-dark)',   'bg' => 'rgba(20,184,166,.16)', 'text' => 'var(--teal-dark)'],
        'amber'  => ['solid' => 'var(--amber-dark)',  'bg' => 'rgba(255,182,39,.20)', 'text' => 'var(--amber-dark)'],
        'violet' => ['solid' => 'var(--violet-dark)', 'bg' => 'rgba(139,92,246,.16)', 'text' => 'var(--violet-dark)'],
    ];
    $wpColor = fn ($slug) => $wpColors[$slug ?? 'coral'] ?? $wpColors['coral'];
@endphp

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonder Park | Family Amusement Center</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <header>
        <nav>
            <div class="logo">
                <img src="{{ asset('images/wonderpark2logo.png') }}" alt="Wonder Park"
                    style="mix-blend-mode: multiply;">
            </div>
            <div class="nav-links">
                <a href="#passes">Day Passes</a>
                <a href="#attractions">Attractions</a>
                <a href="#services">Services</a>
                <a href="#contact">Visit Us</a>
            </div>
            <div class="nav-cta">
                <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Sign In</a>
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Book Now</a>
            </div>
        </nav>
    </header>

    {{-- ================= HERO (CMS: section "hero") ================= --}}
    <section class="hero">
        <video id="wpTeaserVideo" class="hero-bg-video" autoplay muted loop playsinline
            poster="{{ asset('images/wonderpark-teaser-poster.jpg') }}">
            <source src="{{ $hero['video_path'] ?? asset('videos/landing.mp4') }}" type="video/mp4">
        </video>
        <div class="hero-video-overlay"></div>

        <div class="wrap">
            <div class="hero-eyebrow-row">
                <span class="ticket-label on-dark">Field of Rides • Open Daily</span>
            </div>

            <div class="hero-copy">
                <h1>{!! $hero['title'] ?? 'Three worlds of thrill,<br><span class="accent">one ticket away.</span>' !!}</h1>
                <p>
                    {{ $hero['description'] ?? "From the gravity-defying loops of Roller Fever to the prehistoric trails of Dino Adventure, Wonder Park is built for a full day of family fun — now bookable, trackable, and paid for online." }}
                    Passes start as low as
                    <a href="#passes" style="font-weight:700;color:var(--amber);text-decoration:underline;text-underline-offset:3px;">{{ $hero['price_teaser'] ?? '₱149' }}</a>
                    — tap the price to book your slot.
                </p>
                <div class="hero-cta">
                    <a href="#passes" class="btn btn-primary">Reserve a Slot</a>
                    <a href="#attractions" class="btn btn-outline-light">See Attractions</a>
                </div>
                <div class="hero-stamps">
                    <span class="stamp"><span class="swatch" style="background:var(--coral)"></span>Field of Rides</span>
                    <span class="stamp"><span class="swatch" style="background:var(--teal)"></span>Roller Fever</span>
                    <span class="stamp"><span class="swatch" style="background:var(--amber)"></span>Dino Adventure</span>
                </div>
            </div>
        </div>

        <div class="hero-video-meta">
            <span class="hero-video-badge"><span class="dot"></span>At Wonder Park</span>
            <button type="button" class="hero-video-sound" id="wpTeaserSound" aria-label="Toggle video sound"
                onclick="toggleWpSound()">
                <svg id="wpSoundIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                    <line x1="23" y1="9" x2="17" y2="15"></line>
                    <line x1="17" y1="9" x2="23" y2="15"></line>
                </svg>
            </button>
        </div>
    </section>

    {{-- quick-pass strip: built from the same $passes collection as the Passes section --}}
    <div class="quickpass">
        <div class="wrap">
            @foreach ($passes as $pass)
                <a href="#passes" class="quickpass-item" style="color:inherit;">
                    <span class="quickpass-name">
                        <span class="swatch" style="background:{{ ['pass-dino' => 'var(--coral)', 'pass-roller' => 'var(--teal)'][$pass->slug] ?? 'var(--amber)' }}"></span>
                        <span><b>{{ $pass->badge_label }}</b><span>{{ $pass->is_featured ? 'All-day pass' : 'Per-ride / promo' }}</span></span>
                    </span>
                    <span class="quickpass-price">{{ $pass->price_display }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- ================= PASSES (CMS: type "pass") ================= --}}
    <section id="passes" class="tex-cream">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Admission Rates</span>
                <h2>Choose the experience that's right for you</h2>
                <p>Enjoy unlimited fun with our all-day passes. Rates may be updated during special promotions and
                    seasonal events.</p>
            </div>

            <div class="passes stagger">
                @foreach ($passes as $pass)
                    <div class="pass-card {{ $pass->is_featured ? 'featured' : '' }}" onclick="openWpModal('{{ $pass->slug }}')">
                        @if ($pass->is_featured)
                            <span class="ticket-label" style="background:rgba(255,182,39,.20);color:var(--amber-dark);">{{ $pass->badge_label }}</span>
                        @else
                            <span class="ticket-label coral">{{ $pass->badge_label }}</span>
                        @endif
                        <h3>{{ $pass->title }}</h3>
                        <div class="pass-price">{{ $pass->price_display }}<span>/ pass</span></div>
                        <div class="ticket-notch"></div>
                        <ul>
                            @foreach ($pass->features ?? [] as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('login') }}" class="btn {{ $pass->is_featured ? 'btn-teal' : 'btn-amber' }}" style="width:100%;"
                            onclick="event.stopPropagation();">{{ $pass->button_text ?? 'Book Now' }}</a>
                        <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;"
                            onclick="event.stopPropagation(); openWpModal('{{ $pass->slug }}');">See Full Price List</button>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= ATTRACTIONS (CMS: type "attraction") ================= --}}
    <section id="attractions" class="tex-paper">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Attractions</span>
                <h2>Pick your adventure</h2>
                <p>Each zone runs on its own ride roster, staffing, and queue — all tracked in real time through the
                    Wonder Park system.</p>
            </div>

            <div class="carousel-arrows">
                <div class="carousel-arrow" onclick="scrollRides(-360)">&#8592;</div>
                <div class="carousel-arrow" onclick="scrollRides(360)">&#8594;</div>
            </div>

            <div class="rides-carousel-wrap">
                <div class="rides-carousel" id="ridesCarousel">
                    @foreach ($attractions as $ride)
                        <div class="ride-card" @if($ride->button_link) onclick="openWpModal('{{ $ride->slug }}')" style="cursor:pointer;" @endif>
                            <div class="ride-visual" data-bg="{{ $ride->image_path ? asset('storage/' . $ride->image_path) : asset('images/placeholder-ride.jpg') }}">
                                <span class="ride-badge {{ $ride->icon ?: 'coral' }}">{{ $ride->badge_label }}</span>
                            </div>
                            <div class="ride-info">
                                <h4>{{ $ride->title }}</h4>
                                <p>{{ $ride->description }}</p>
                                @if (!empty($ride->features))
                                    <div class="ride-meta">
                                        @foreach ($ride->features as $meta)
                                            <span>{{ $meta }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if (!empty($ride->modal_list))
                                    <ul class="ride-policy">
                                        @foreach ($ride->modal_list as $policy)
                                            <li>{{ $policy }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FULL DAY OUT / split section (CMS: section "split") ================= --}}
    <section class="split-section tex-cream">
        <div class="wrap">
            <div class="split-grid reveal">
                <div class="split-visual">
                    <video class="split-video" autoplay muted loop playsinline>
                        <source src="{{ asset('videos/landing.mp4') }}" type="video/mp4">
                    </video>
                    <span class="split-tag">{{ $split['location_tag'] ?? 'Lima Technology Center · Lipa City / Malvar' }}</span>
                </div>
                <div class="split-copy">
                    <span class="ticket-label violet">{{ $split['tag'] ?? 'Plan Your Day' }}</span>
                    <h2>{{ $split['title'] ?? 'Make it a full day out — not just a stop-by' }}</h2>
                    <p>{{ $split['description'] ?? "Wonder Park sits inside the Lima Technology Center complex, so a booking here pairs easily with a longer family day: grab a meal nearby, then swing through all three zones before closing." }}</p>
                    <ul class="split-list">
                        <li>Open daily, including holidays — no need to plan around a rest day</li>
                        <li>Weekend hours run longer (10AM–9PM) for full-day visits</li>
                        <li>Group bookings and school field trips are coordinated in advance</li>
                        <li>Walk-ins welcome across all three zones, subject to capacity</li>
                    </ul>
                    <a href="#passes" class="btn btn-primary">Choose a Pass</a>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= GUEST SERVICES (CMS: type "service") ================= --}}
    <section id="services" class="tex-paper">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label amber">Guest Services</span>
                <h2>Everything you need for a fun-filled visit</h2>
                <p>Whether you're planning a birthday celebration, family day out, or group event, Wonder Park offers
                    exciting attractions, party packages, convenient payment options, and easy booking channels.</p>
            </div>

            <div class="modules stagger">
                @foreach ($services as $service)
                    <div class="module-card" onclick="openWpModal('{{ $service->slug }}')">
                        <div class="module-icon">{{ $service->icon ?? '✨' }}</div>
                        <h3>{{ $service->title }}</h3>
                        <p>{{ $service->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= HOW IT WORKS (CMS: type "step") ================= --}}
    <section class="howitworks">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label on-dark">How it works</span>
                <h2>From booking to gate in 3 steps</h2>
            </div>
            <div class="steps stagger">
                @foreach ($steps as $i => $step)
                    <div class="step" onclick="openWpModal('{{ $step->slug }}')">
                        <div class="step-circle" style="background:{{ [ 'var(--coral)', 'var(--amber)', 'var(--teal)' ][$i % 3] }};">{{ $i + 1 }}</div>
                        <h4>{{ $step->title }}</h4>
                        <p>{{ $step->description }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= CONTACT (CMS: section "contact") ================= --}}
    <section id="contact" class="tex-cream">
        <div class="wrap">
            <div class="contact reveal">
                <div>
                    <span class="ticket-label coral">Visit Us</span>
                    <h2>Plan your visit to Wonder Park</h2>
                    <p>Open daily, including holidays. Group bookings and school field trips are coordinated in
                        advance through our reservations team.</p>
                    <a href="{{ route('login') }}" class="btn btn-primary">Book a Slot</a>
                </div>
                <div>
                    <div class="info-row"><b>Location</b><span>{{ $contact['location'] ?? 'Lima Technology Center, Lipa City/Malvar, Batangas' }}</span></div>
                    <div class="info-row">
                        <b>Hours</b>
                        <span>{{ $contact['hours_weekend'] ?? '10:00 AM – 9:00 PM Weekends' }}</span>
                        <span>{{ $contact['hours_weekday'] ?? '11:00 AM – 9:00 PM Weekdays' }}</span>
                    </div>
                    <div class="info-row"><b>Zones</b><span>{{ $contact['zones'] ?? 'Field of Rides · Roller Fever · Dino Adventure' }}</span></div>
                    <div class="info-row"><b>Bookings</b><span>Online reservation or walk-in (subject to capacity)</span></div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= FOOTER (CMS: section "footer") ================= --}}
    <footer>
        <div class="wrap">
            <h3>Wonder Park</h3>
            <p>{{ $footer['tagline'] ?? 'From thrilling rides to prehistoric adventures and endless skating fun, Wonder Park is your destination for unforgettable family experiences.' }}</p>
            <div class="footer-zones">
                <span><span class="swatch" style="background:var(--coral)"></span>Field of Rides</span>
                <span><span class="swatch" style="background:var(--teal)"></span>Roller Fever</span>
                <span><span class="swatch" style="background:var(--amber)"></span>Dino Adventure</span>
            </div>
            <p class="copyright">© {{ date('Y') }} Wonder Park. All Rights Reserved.</p>
        </div>
    </footer>

    {{-- ---- Reusable preview modal ---- --}}
    <div class="wp-modal-overlay" id="wpModalOverlay" onclick="if(event.target===this) closeWpModal()">
        <div class="wp-modal">
            <button class="wp-modal-close" onclick="closeWpModal()">&times;</button>
            <span class="wp-modal-icon" id="wpModalIcon"></span>
            <span class="wp-modal-tag" id="wpModalTag"></span>
            <h3 id="wpModalTitle"></h3>
            <p class="wp-modal-desc" id="wpModalDesc"></p>
            <ul id="wpModalList"></ul>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm"
                style="display:block;width:100%;text-align:center;margin-top:18px;box-sizing:border-box;">Book Now</a>
        </div>
    </div>

    <script>
        // Background images
        document.querySelectorAll('[data-bg]').forEach(el => {
            el.style.backgroundImage = `url('${el.dataset.bg}')`;
        });

        // Rides carousel
        function scrollRides(amount) {
            document.getElementById('ridesCarousel').scrollBy({
                left: amount,
                behavior: 'smooth'
            });
        }

        // Teaser video sound toggle
        function toggleWpSound() {
            const vid = document.getElementById('wpTeaserVideo');
            const icon = document.getElementById('wpSoundIcon');
            vid.muted = !vid.muted;
            icon.innerHTML = vid.muted ?
                '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><line x1="23" y1="9" x2="17" y2="15"></line><line x1="17" y1="9" x2="23" y2="15"></line>' :
                '<polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path><path d="M19.07 4.93a10 10 0 0 1 0 14.14"></path>';
        }

        // Scroll reveal via IntersectionObserver
        const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .stagger');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        revealEls.forEach(el => observer.observe(el));
        const wpModalData = {
    @foreach ($passes->concat($attractions)->concat($services)->concat($steps) as $item)
        '{{ $item->slug }}': {
            iconType: @json($item->icon_type ?? null),
            icon: @json($item->icon),
            iconImage: @json(!empty($item->icon_image_path) ? asset('storage/' . $item->icon_image_path) : null),
            tag: @json($item->badge_label),
            tagColor: @json($wpColor($item->badge_color ?? null)['bg']),
            tagText: @json($wpColor($item->badge_color ?? null)['text']),
            title: @json($item->title),
            desc: @json($item->description),
            list: @json($item->modal_list ?? [])
        },
    @endforeach
        };

function openWpModal(key) {
    const data = wpModalData[key];
    if (!data) return;

    const iconEl = document.getElementById('wpModalIcon');
    if (data.iconType === 'image' && data.iconImage) {
        iconEl.innerHTML = `<img src="${data.iconImage}" alt="" style="width:34px;height:34px;border-radius:8px;object-fit:cover;">`;
    } else if (data.icon && /^[a-z0-9-]+$/i.test(data.icon) && data.iconType !== null) {
        // FontAwesome slug (pass/attraction cards use icon_type = 'fa')
        iconEl.innerHTML = `<i class="fa-solid fa-${data.icon}"></i>`;
    } else {
        // raw emoji/text icon (services/steps don't use icon_type)
        iconEl.textContent = data.icon || '';
    }

    const tagEl = document.getElementById('wpModalTag');
    tagEl.textContent = data.tag || '';
    tagEl.style.background = data.tagColor;
    tagEl.style.color = data.tagText;

    document.getElementById('wpModalTitle').textContent = data.title;
    document.getElementById('wpModalDesc').textContent = data.desc;

    const listEl = document.getElementById('wpModalList');
    listEl.innerHTML = '';
    (data.list || []).forEach(item => {
        const li = document.createElement('li');
        li.textContent = item;
        listEl.appendChild(li);
    });

    document.getElementById('wpModalOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeWpModal() {
    document.getElementById('wpModalOverlay').classList.remove('open');
    document.body.style.overflow = '';
}
       </script>

</body>

</html>
