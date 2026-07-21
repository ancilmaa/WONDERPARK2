
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wonder Park | Family Amusement Center</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/landing.css')); ?>">
    <style>
        /* Colorful module card top borders */
        .modules .module-card:nth-child(6n+1) {
            border-top: 4px solid var(--coral);
        }

        .modules .module-card:nth-child(6n+2) {
            border-top: 4px solid var(--amber);
        }

        .modules .module-card:nth-child(6n+3) {
            border-top: 4px solid var(--teal);
        }

        .modules .module-card:nth-child(6n+4) {
            border-top: 4px solid #8b5cf6;
        }

        .modules .module-card:nth-child(6n+5) {
            border-top: 4px solid var(--coral);
        }

        .modules .module-card:nth-child(6n+6) {
            border-top: 4px solid var(--amber);
        }

        /* Colorful step circles */
        .steps .step:nth-child(1) .step-circle {
            background: var(--coral);
        }

        .steps .step:nth-child(2) .step-circle {
            background: var(--amber);
        }

        .steps .step:nth-child(3) .step-circle {
            background: var(--teal);
        }

        .steps .step:nth-child(4) .step-circle {
            background: #8b5cf6;
        }

        /* ---- Center the Guest Services & How-it-works cards ---- */
        .modules.stagger {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 24px;
        }

        .modules.stagger .module-card {
            flex: 0 1 280px;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .modules.stagger .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .10);
        }

        .steps.stagger {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 32px;
        }

        .steps.stagger .step {
            flex: 0 1 220px;
            text-align: center;
            cursor: pointer;
            transition: transform .2s ease;
        }

        .steps.stagger .step:hover {
            transform: translateY(-4px);
        }

        /* ---- Pass card hover + secondary button ---- */
        .pass-card {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .pass-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, .10);
        }

        .btn-outline {
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            font-size: inherit;
        }

        /* ---- Preview modal ---- */
        .wp-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(20, 15, 10, .55);
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .wp-modal-overlay.open {
            display: flex;
        }

        .wp-modal {
            background: #fff;
            border-radius: 18px;
            max-width: 460px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            padding: 28px 26px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .25);
            animation: wpModalIn .18s ease;
        }

        @keyframes wpModalIn {
            from {
                opacity: 0;
                transform: translateY(12px) scale(.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .wp-modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            border: none;
            background: #f1f1f1;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 18px;
            line-height: 1;
            cursor: pointer;
            color: #555;
        }

        .wp-modal-close:hover {
            background: #e5e5e5;
        }

        .wp-modal-icon {
            font-size: 38px;
            margin-bottom: 6px;
            display: block;
        }

        .wp-modal-tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .03em;
            padding: 4px 10px;
            border-radius: 999px;
            margin-bottom: 10px;
        }

        .wp-modal h3 {
            margin: 0 0 10px;
            font-family: "Baloo 2", sans-serif;
            font-size: 22px;
        }

        .wp-modal p.wp-modal-desc {
            margin: 0 0 16px;
            color: #555;
            line-height: 1.55;
        }

        .wp-modal ul {
            margin: 0;
            padding-left: 18px;
            color: #444;
            line-height: 1.7;
        }

        .wp-modal ul li {
            margin-bottom: 4px;
        }

        /* ---- Shopping-run split section (image left, text right) ---- */
        .split-section {
            padding: 70px 0;
        }

        .split-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }

        .split-visual {
            background-size: cover;
            background-position: center;
            border-radius: 20px;
            min-height: 340px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, .12);
        }

        .split-copy h2 {
            margin: 10px 0 16px;
        }

        .split-copy p {
            color: #555;
            line-height: 1.65;
            margin-bottom: 22px;
        }

        @media (max-width:820px) {
            .split-grid {
                grid-template-columns: 1fr;
            }

            .split-visual {
                min-height: 240px;
                order: -1;
            }
        }
    </style>
</head>

<body>

    <header>
        <nav>
            <div class="logo">
                <img src="<?php echo e(asset('images/wonderpark2logo.png')); ?>" alt="Wonder Park"
                    style="height:50px;vertical-align:middle; margin:0; mix-blend-mode: multiply;">
            </div>
            <div class="nav-links">
                <a href="#passes">Day Passes</a>
                <a href="#attractions">Attractions</a>
                <a href="#services">Services</a>
                <a href="#contact">Visit Us</a>
            </div>
            <div style="display:flex;gap:10px;">
                <a href="<?php echo e(route('login')); ?>" class="btn btn-outline btn-sm">Sign In</a>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-sm">Book Now</a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <video id="wpTeaserVideo" class="hero-bg-video" autoplay muted loop playsinline
            poster="<?php echo e(asset('images/wonderpark-teaser-poster.jpg')); ?>">
            <source src="<?php echo e(asset('videos/landing.mp4')); ?>" type="video/mp4">
        </video>
        <div class="hero-video-overlay"></div>

        <div class="wrap">
            <div class="hero-grid">

                <div class="hero-copy">
                    <div class="hero-eyebrow-row">
                        <span class="ticket-label coral">Field of Rides • Open Daily</span>
                    </div>
                    <h1>Three worlds of thrill,<br><span class="accent">one ticket away.</span></h1>
                    <p>
                        From the gravity-defying loops of Roller Fever to the prehistoric trails of Dino Adventure,
                        Wonder Park is built for a full day of family fun — now bookable, trackable, and paid for
                        online.
                        Passes start as low as
                        <a href="#passes"
                            style="font-weight:700;color:var(--amber);text-decoration:underline;text-underline-offset:3px;">₱149</a>
                        — tap the price to book your slot.
                    </p>
                    <div class="hero-cta">
                        <a href="#passes" class="btn btn-primary">Reserve a Slot</a>
                        <a href="#attractions" class="btn btn-outline-light">See Attractions</a>
                    </div>
                    <div class="hero-stamps">
                        <span class="stamp"><span class="swatch" style="background:var(--coral)"></span>Field of
                            Rides</span>
                        <span class="stamp"><span class="swatch" style="background:var(--teal)"></span>Roller
                            Fever</span>
                        <span class="stamp"><span class="swatch" style="background:var(--amber)"></span>Dino
                            Adventure</span>
                    </div>
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
    

    <section id="passes">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Admission Rates</span>
                <h2>Choose the experience that's right for you</h2>
                <p>Enjoy unlimited fun with our all-day passes. Rates may be updated during special promotions and
                    seasonal events.</p>
            </div>

            <div class="passes stagger">

                <div class="pass-card" onclick="openWpModal('pass-dino')" style="cursor:pointer;">
                    <span class="ticket-label amber">Dino Adventure</span>
                    <h3>Dino Day Pass</h3>
                    <div class="pass-price">₱599<span>/ pass</span></div>
                    <ul>
                        <li>Unlimited all-day access</li>
                        <li>Includes 1 child + 1 guardian</li>
                        <li>Access to all Dino Adventure play areas</li>
                        <li>Perfect for ages 4–12</li>
                    </ul>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-amber" style="width:100%;"
                        onclick="event.stopPropagation();">Book Now</a>
                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;"
                        onclick="event.stopPropagation(); openWpModal('pass-dino');">See Full Price List</button>
                </div>

                <div class="pass-card featured" onclick="openWpModal('pass-roller')" style="cursor:pointer;">
                    <span class="ticket-label" style="background:rgba(255,182,39,.18);color:var(--amber);">Most
                        Popular</span>
                    <h3>Roller Fever Pass</h3>
                    <div class="pass-price">₱599<span>/ pass</span></div>
                    <ul>
                        <li>Unlimited all-day skating access</li>
                        <li>Open for kids, teens, and adults</li>
                        <li>Great for families and groups</li>
                        <li>Skate rental included</li>
                    </ul>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-teal" style="width:100%;"
                        onclick="event.stopPropagation();">Book Now</a>
                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;"
                        onclick="event.stopPropagation(); openWpModal('pass-roller');">See Full Price List</button>
                </div>

                <div class="pass-card" onclick="openWpModal('pass-fields')" style="cursor:pointer;">
                    <span class="ticket-label coral">Field of Rides</span>
                    <h3>Ride-All-You-Can</h3>
                    <div class="pass-price">Promo</div>
                    <ul>
                        <li>Ride-all-you-can packages available</li>
                        <li>Special discounts via Klook & StarDeals</li>
                        <li>Seasonal and event-based promotions</li>
                        <li>Ask our staff for current rates</li>
                    </ul>
                    <a href="#" class="btn btn-coral" style="width:100%;"
                        onclick="event.stopPropagation();">View Promos</a>
                    <button type="button" class="btn btn-outline" style="width:100%;margin-top:8px;"
                        onclick="event.stopPropagation(); openWpModal('pass-fields');">See Per-Ride Prices</button>
                </div>

            </div>
        </div>
    </section>

    <section id="attractions">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label coral">Attractions</span>
                <h2>Pick your adventure</h2>
                <p>Each zone runs on its own ride roster, staffing, and queue — all tracked in real time through the
                    Wonder Park system.</p>
            </div>

            <div class="carousel-arrows">
                <div class="carousel-arrow" onclick="scrollRides(-340)">&#8592;</div>
                <div class="carousel-arrow" onclick="scrollRides(340)">&#8594;</div>
            </div>

            <div class="rides-carousel-wrap">
                <div class="rides-carousel" id="ridesCarousel">

                    <div class="ride-card">
                        <div class="ride-visual" data-bg="<?php echo e(asset('images/vikings.jpg')); ?>">
                            <span class="ride-badge coral">Field of Rides</span>
                        </div>
                        <div class="ride-info">
                            <h4>Field of Rides</h4>
                            <p>Enjoy exciting amusement rides ranging from family attractions to high-thrill experiences
                                designed for adventure seekers.</p>
                            <div class="ride-meta">
                                <span>Multiple Attractions</span>
                                <span>Ride-Specific Rules</span>
                            </div>
                            <ul class="ride-policy">
                                <li>Ride eligibility depends on each attraction's height and safety requirements.</li>
                                <li>Guests with heart conditions, severe asthma, epilepsy, recent injuries, or similar
                                    medical concerns should avoid extreme rides.</li>
                                <li>Pregnant guests are not permitted on extreme or high-impact attractions.</li>
                                <li>Guests below 18 years old require parent or guardian consent.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="ride-card">
                        <div class="ride-visual" data-bg="<?php echo e(asset('images/roller-fever.jpg')); ?>">
                            <span class="ride-badge teal">Roller Fever</span>
                        </div>
                        <div class="ride-info">
                            <h4>Roller Fever Skating Rink</h4>
                            <p>Experience all-day roller skating fun in a safe and family-friendly environment. Perfect
                                for beginners and experienced skaters alike.</p>
                            <div class="ride-meta">
                                <span>All-Day Access</span>
                                <span>Family Friendly</span>
                            </div>
                            <ul class="ride-policy">
                                <li>Children aged 4–6 years old must be accompanied by a guardian inside the skating
                                    area.</li>
                                <li>Guardians entering the rink must also wear roller skates.</li>
                                <li>Guests below 18 years old require parent or guardian consent.</li>
                                <li>Consent may be provided in person or through call, text message, or online chat
                                    confirmation.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="ride-card">
                        <div class="ride-visual" data-bg="<?php echo e(asset('images/dino_1.jpg')); ?>">
                            <span class="ride-badge amber">Dino Adventure</span>
                        </div>
                        <div class="ride-info">
                            <h4>Dino Adventure Playground</h4>
                            <p>Explore a prehistoric-themed indoor playground featuring slides, climbing areas, obstacle
                                courses, and interactive play zones.</p>
                            <div class="ride-meta">
                                <span>Max Age: 12</span>
                                <span>Indoor Playground</span>
                            </div>
                            <ul class="ride-policy">
                                <li>Children aged 1–5 years old must always be accompanied by a guardian.</li>
                                <li>Only children 12 years old and below are allowed to use the play facilities.</li>
                                <li>Guardians are responsible for supervising young children at all times.</li>
                                <li>Guests below 18 years old require parent or guardian consent.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="ride-card" onclick="openWpModal('celebrate')" style="cursor:pointer;">
                        <div class="ride-visual" data-bg="<?php echo e(asset('images/roller-skates.jpg')); ?>">
                            <span class="ride-badge teal">Roller Fever</span>
                            <span class="ride-badge amber" style="left:auto;right:12px;">Dino Adventure</span>
                        </div>
                        <div class="ride-info">
                            <h4>Celebrate at Wonder Park</h4>
                            <p>Both Roller Fever and Dino Adventure welcome birthdays and group celebrations — skate
                                under the neon photo-booth corner or gather the kids around the giant ball pit inside
                                the prehistoric playhouse.</p>
                            <div class="ride-meta">
                                <span>All ages</span>
                                <span>Party Add-on</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>


    <section id="services">
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label amber">Guest Services</span>
                <h2>Everything you need for a fun-filled visit</h2>
                <p>Whether you're planning a birthday celebration, family day out, or group event, Wonder Park offers
                    exciting attractions, party packages, convenient payment options, and easy booking channels.</p>
            </div>

            <div class="modules stagger">

                <div class="module-card" onclick="openWpModal('party')">
                    <div class="module-icon">🎉</div>
                    <h3>Birthday & Party Packages</h3>
                    <p>Celebrate special occasions at Field of Rides, Dino Adventure, or Roller Fever. Party packages
                        are available for birthdays, school groups, team-building events, and family gatherings.</p>
                </div>

                <div class="module-card" onclick="openWpModal('reservation')">
                    <div class="module-icon">📝</div>
                    <h3>Reservation & Confirmation</h3>
                    <p>Simply submit your booking request and event details. Reservations are subject to availability
                        and will be confirmed by our team after review.</p>
                </div>

                <div class="module-card" onclick="openWpModal('channels')">
                    <div class="module-icon">🌐</div>
                    <h3>Multiple Booking Channels</h3>
                    <p>Guests may book directly with Wonder Park or through our trusted booking partners including Klook
                        and StarDeals for selected attractions, promos, and packages.</p>
                </div>

                <div class="module-card" onclick="openWpModal('payment')">
                    <div class="module-icon">💳</div>
                    <h3>Flexible Payment Options</h3>
                    <p>We accept Cash, Credit Card, Debit Card, GCash, Maya, Klook vouchers, and StarDeals vouchers for
                        a convenient and hassle-free experience.</p>
                </div>

                <div class="module-card" onclick="openWpModal('snacks')">
                    <div class="module-icon">🍿</div>
                    <h3>Snack Bar & Refreshments</h3>
                    <p>Enjoy a variety of snacks, drinks, and refreshments available inside the venue so guests can stay
                        energized throughout their adventure.</p>
                </div>

            </div>
        </div>
    </section>


    <section>
        <div class="wrap">
            <div class="section-head reveal">
                <span class="ticket-label violet">How it works</span>
                <h2>From booking to gate in 4 steps</h2>
            </div>
            <div class="steps stagger">
                <div class="step" onclick="openWpModal('step-book')">
                    <div class="step-circle">1</div>
                    <h4>Book online</h4>
                    <p>Choose your date, zone, and headcount.</p>
                </div>
                <div class="step" onclick="openWpModal('step-scan')">
                    <div class="step-circle">2</div>
                    <h4>Scan at the gate</h4>
                    <p>Show your e-ticket — POS verifies it instantly.</p>
                </div>
                <div class="step" onclick="openWpModal('step-ride')">
                    <div class="step-circle">3</div>
                    <h4>Ride all day</h4>
                    <p>Enjoy Field of Rides, Roller Fever, and Dino Adventure access.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="contact">
        <div class="wrap">
            <div class="contact reveal">
                <div>
                    <h2>Plan your visit to Wonder Park</h2>
                    <p>Open daily, including holidays. Group bookings and school field trips are coordinated in advance
                        through our reservations team.</p>
                    <a href="<?php echo e(route('login')); ?>" class="btn btn-primary">Book a Slot</a>
                </div>
                <div>
                    <div class="info-row"><b>Location</b><span>Lima Technology Center, Lipa City/Malvar,
                            Batangas</span></div>
                    <div class="info-row"><b>Hours</b><span>10:00 AM – 9:00 PM Weekends</span><span>11:00 AM – 9:00 PM
                            Weekdays</span></div>
                    <div class="info-row"><b>Zones</b><span>Field of Rides · Roller Fever · Dino Adventure</span></div>
                    <div class="info-row"><b>Bookings</b><span>Online reservation or walk-in (subject to
                            capacity)</span></div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="wrap">
            <h3>Wonder Park</h3>
            <p>From thrilling rides to prehistoric adventures and endless skating fun, Wonder Park is your destination
                for unforgettable family experiences.</p>
            <div class="footer-zones">
                <span><span class="swatch" style="background:var(--coral)"></span>Field of Rides</span>
                <span><span class="swatch" style="background:var(--teal)"></span>Roller Fever</span>
                <span><span class="swatch" style="background:var(--amber)"></span>Dino Adventure</span>
            </div>
            <p class="copyright">© <?php echo e(date('Y')); ?> Wonder Park. All Rights Reserved.</p>
        </div>
    </footer>

    
    <div class="wp-modal-overlay" id="wpModalOverlay" onclick="if(event.target===this) closeWpModal()">
        <div class="wp-modal">
            <button class="wp-modal-close" onclick="closeWpModal()">&times;</button>
            <span class="wp-modal-icon" id="wpModalIcon"></span>
            <span class="wp-modal-tag" id="wpModalTag"></span>
            <h3 id="wpModalTitle"></h3>
            <p class="wp-modal-desc" id="wpModalDesc"></p>
            <ul id="wpModalList"></ul>
            <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-sm"
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
        }, {
            threshold: 0.12
        });
        revealEls.forEach(el => observer.observe(el));

        // ---- Preview modal content & logic ----
        const wpModalData = {
            'pass-dino': {
                icon: '🦕',
                tag: 'Dino Adventure',
                tagColor: 'rgba(255,182,39,.18)',
                tagText: 'var(--amber)',
                title: 'Dino Adventure Price List',
                desc: 'Prehistoric-themed indoor playground rates. All-day pass gives unlimited access for the whole day.',
                list: [
                    '• 1 Hour — ₱299',
                    '• 2 Hours — ₱399',
                    '• All Day Pass — ₱599',
                    '• Guardian entry — ₱50',
                    '• Additional 30 mins — ₱149',
                    '• Additional hour — ₱199'
                ]
            },
            'pass-roller': {
                icon: '🛼',
                tag: 'Roller Fever',
                tagColor: 'rgba(45,212,191,.15)',
                tagText: 'var(--teal)',
                title: 'Roller Fever Price List',
                desc: 'Regular rates and group bundle discounts for skating sessions. Skates and gear rental available separately.',
                list: [
                    '• Regular — 1 Hour: ₱249',
                    '• Regular — 2 Hours: ₱399',
                    '• Regular — All Day Pass: ₱599',
                    '• Socks — ₱50',
                    '• Group Bundle (4+1) — 1 Hour: ₱996',
                    '• Group Bundle (4+1) — 2 Hours: ₱1,596',
                    '• Skates and Gears rental — ₱50',
                    '• Birthday, group parties & company events — inquire inside'
                ]
            },
            'pass-fields': {
                icon: '🎡',
                tag: 'Field of Rides',
                tagColor: 'rgba(255,107,107,.15)',
                tagText: 'var(--coral)',
                title: 'Field of Rides — Per-Ride Prices',
                desc: 'Each ride is priced individually per head, except for car-based and per-ride attractions. Promo bundles may be available seasonally.',
                list: [
                    '• Tiger Train — ₱60 per head',
                    '• Mini Carousel — ₱60 per head',
                    '• Star Speed — ₱60 per head',
                    '• Little Chicken — ₱60 per head',
                    '• Boat Pool — ₱60 per head',
                    '• Carousel — ₱60 per head',
                    '• Flying Chair — ₱60 per head',
                    '• Mini Ferris Wheel — ₱60 per head',
                    '• Samba Balloon — ₱60 per head',
                    '• Crazy Plane — ₱60 per head',
                    '• Vikings — ₱120 per head',
                    '• Go-Kart — ₱120 per head',
                    '• Inflatable Playground — ₱150 per head',
                    '• Mini Trampoline — ₱150 per 30 mins',
                    '• Rev & Roll — ₱150 per car',
                    '• Happy Cars — ₱150 per car',
                    '• Jurassic Adventure — ₱150 per ride'
                ]
            },
            'party': {
                icon: '🎉',
                tag: 'Guest Services',
                tagColor: 'rgba(255,107,107,.15)',
                tagText: 'var(--coral)',
                title: 'Birthday & Party Packages',
                desc: 'Turn any birthday, school outing, or team-building day into a full Wonder Park celebration. Packages can be mixed across all three zones depending on your group\'s age and interests.',
                list: [
                    '• Available at Field of Rides, Dino Adventure, or Roller Fever',
                    '• Options for small family celebrations up to large group events',
                    '• Suited for birthdays, school field trips, and team-building activities',
                    '• Coordinated in advance with our reservations team',
                    '• Ask staff about add-ons like reserved seating or the Skate & Celebrate photo corner'
                ]
            },
            'reservation': {
                icon: '📝',
                tag: 'Guest Services',
                tagColor: 'rgba(255,182,39,.18)',
                tagText: 'var(--amber)',
                title: 'Reservation & Confirmation',
                desc: 'Booking a slot is simple — submit your preferred date, zone, and headcount, and our team takes care of the rest.',
                list: [
                    '• Submit your booking request with event details online',
                    '• Reservations are subject to slot availability',
                    '• Our team reviews and confirms each request',
                    '• Confirmation is sent once your slot is secured',
                    '• Group and school bookings should be made in advance'
                ]
            },
            'channels': {
                icon: '🌐',
                tag: 'Guest Services',
                tagColor: 'rgba(45,212,191,.15)',
                tagText: 'var(--teal)',
                title: 'Multiple Booking Channels',
                desc: 'Book however is most convenient for you — directly through Wonder Park, or through one of our trusted partners.',
                list: [
                    '• Book directly on the Wonder Park website for full flexibility',
                    '• Book through Klook for select attractions and promos',
                    '• Book through StarDeals for seasonal discounts and vouchers',
                    '• Voucher-based bookings are redeemed at the gate',
                    '• Rates and inclusions may vary slightly by channel'
                ]
            },
            'payment': {
                icon: '💳',
                tag: 'Guest Services',
                tagColor: 'rgba(139,92,246,.15)',
                tagText: '#8b5cf6',
                title: 'Flexible Payment Options',
                desc: 'Wonder Park accepts a wide range of payment methods so you can pay however works best for you, online or at the gate.',
                list: [
                    '• Cash — accepted at the gate and on-site counters',
                    '• Credit Card & Debit Card',
                    '• GCash and Maya e-wallets',
                    '• Klook vouchers and StarDeals vouchers',
                    '• Online payments are confirmed instantly upon checkout'
                ]
            },
            'snacks': {
                icon: '🍿',
                tag: 'Guest Services',
                tagColor: 'rgba(255,107,107,.15)',
                tagText: 'var(--coral)',
                title: 'Snack Bar & Refreshments',
                desc: 'Keep the energy up all day with snacks and drinks available right inside the venue — no need to step out.',
                list: [
                    '• Snacks, drinks, and light meals available on-site',
                    '• Conveniently located within the venue',
                    '• Open throughout regular park hours',
                    '• Great stop between zones or during party bookings'
                ]
            },
            'celebrate': {
                icon: '🎉',
                tag: 'Party Add-on',
                tagColor: 'rgba(45,212,191,.15)',
                tagText: 'var(--teal)',
                title: 'Celebrate at Wonder Park',
                desc: 'Both Roller Fever and Dino Adventure double as ready-made party venues — pick the vibe that fits your celebration and let our team handle the setup.',
                list: [
                    '• Roller Fever: neon photo-booth corner, perfect for skate parties',
                    '• Dino Adventure: giant ball pit inside the prehistoric playhouse',
                    '• Great for birthdays, kiddie parties, and group celebrations',
                    '• Open to all ages — mix and match zones for bigger groups',
                    '• Book in advance through our reservations team to lock in your date'
                ]
            },
            'step-book': {
                icon: '1️⃣',
                tag: 'How it works',
                tagColor: 'rgba(255,107,107,.15)',
                tagText: 'var(--coral)',
                title: 'Step 1 — Book Online',
                desc: 'Start your visit by reserving your slot before you arrive — it only takes a few minutes.',
                list: [
                    '• Pick your preferred date and time',
                    '• Choose your zone: Field of Rides, Roller Fever, or Dino Adventure',
                    '• Enter your headcount, including children and guardians',
                    '• Receive an e-ticket once your booking is confirmed',
                    '• Walk-ins are welcome too, subject to capacity'
                ]
            },
            'step-scan': {
                icon: '2️⃣',
                tag: 'How it works',
                tagColor: 'rgba(255,182,39,.18)',
                tagText: 'var(--amber)',
                title: 'Step 2 — Scan at the Gate',
                desc: 'No printing needed — just show your e-ticket on your phone and our POS system verifies it instantly.',
                list: [
                    '• Present your e-ticket QR code at the entrance',
                    '• Staff scans and validates it on the spot',
                    '• Guests below 18 need parent or guardian consent',
                    '• Consent can be given in person, by call, text, or online chat',
                    '• Keep your ticket handy in case re-verification is needed'
                ]
            },
            'step-ride': {
                icon: '3️⃣',
                tag: 'How it works',
                tagColor: 'rgba(45,212,191,.15)',
                tagText: 'var(--teal)',
                title: 'Step 3 — Ride All Day',
                desc: 'Once you\'re in, your pass unlocks unlimited access to your chosen zone for the rest of the day.',
                list: [
                    '• Enjoy unlimited access within your booked zone',
                    '• Ride eligibility follows each attraction\'s height and safety rules',
                    '• Guests with certain health conditions should avoid extreme rides',
                    '• Guardians must accompany young children per zone-specific rules',
                    '• Valid until park closing time on your visit date'
                ]
            }
        };

        function openWpModal(key) {
            const data = wpModalData[key];
            if (!data) return;

            document.getElementById('wpModalIcon').textContent = data.icon;
            const tagEl = document.getElementById('wpModalTag');
            tagEl.textContent = data.tag;
            tagEl.style.background = data.tagColor;
            tagEl.style.color = data.tagText;
            document.getElementById('wpModalTitle').textContent = data.title;
            document.getElementById('wpModalDesc').textContent = data.desc;

            const listEl = document.getElementById('wpModalList');
            listEl.innerHTML = '';
            data.list.forEach(item => {
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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeWpModal();
        });
    </script>

</body>

</html>
<?php /**PATH C:\xampp\htdocs\WONDERPARK\WONDERPARK2\laravel-app\resources\views/landing.blade.php ENDPATH**/ ?>