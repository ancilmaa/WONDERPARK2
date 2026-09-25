@extends('layouts.sidebar')

@section('title', ucfirst($section) . ' Section')

@section('styles')
<style>
    /* ============================================================
       CMS section editor — breadcrumb + header actions, a config
       panel, and a full-width "customer facing" live preview below
       it. The preview now renders a different layout per $section
       (hero / split / contact / footer), each one mirroring the
       matching block in resources/views/landing.blade.php + the
       shared design system in public/css/landing.css — instead of
       always rendering the hero video layout regardless of which
       section is being edited.
       ============================================================ */

    .cms-page {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    /* ---------- breadcrumb ---------- */
    .cms-crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: .78rem;
        color: var(--muted);
    }

    .cms-crumb a {
        color: var(--muted);
        text-decoration: none;
    }

    .cms-crumb a:hover {
        color: var(--pink-dark);
    }

    .cms-crumb i {
        font-size: .6rem;
        color: var(--line-strong);
    }

    .cms-crumb .is-current {
        color: var(--ink-soft);
        font-weight: 600;
    }

    /* ---------- header ---------- */
    .cms-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .cms-top h1 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--ink);
        margin: 2px 0 6px;
    }

    .cms-top p {
        font-size: .86rem;
        color: var(--muted);
        margin: 0;
        max-width: 60ch;
    }

    .cms-top-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
    }

    .cms-btn-ghost,
    .cms-btn-solid {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-family: inherit;
        font-size: .82rem;
        font-weight: 700;
        padding: 9px 16px;
        border-radius: 9px;
        cursor: pointer;
        white-space: nowrap;
    }

    .cms-btn-ghost {
        background: var(--card);
        color: var(--ink-soft);
        border: 1.5px solid var(--line-strong);
    }

    .cms-btn-ghost:hover {
        border-color: var(--pink);
        color: var(--pink-dark);
    }

    .cms-btn-solid {
        background: var(--pink);
        color: #fff;
        border: none;
    }

    .cms-btn-solid:hover {
        background: var(--pink-dark);
    }

    /* ---------- alerts ---------- */
    .cms-alert {
        padding: 12px 16px;
        border-radius: 10px;
        font-size: .85rem;
        font-weight: 600;
    }

    .cms-alert--success {
        background: var(--green-light);
        color: var(--green);
    }

    .cms-alert--error {
        background: var(--pink-light);
        color: var(--pink-deep);
    }

    /* ---------- config panel ---------- */
    .cms-panel {
        background: var(--card);
        border-radius: 16px;
        border: 1px solid var(--line);
        overflow: hidden;
    }

    .cms-panel-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 16px 24px;
        border-bottom: 1px solid var(--line);
    }

    .cms-panel-title {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .05em;
        color: var(--ink);
    }

    .cms-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--pink);
        flex-shrink: 0;
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .18);
    }

    .cms-panel-hint {
        font-size: .76rem;
        color: var(--muted);
    }

    .cms-panel form {
        padding: 24px;
    }

    .cms-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 22px 18px;
    }

    @media (max-width: 640px) {
        .cms-fields-grid {
            grid-template-columns: 1fr;
        }
    }

    .cms-field {
        margin: 0;
    }

    .cms-field-span {
        grid-column: 1 / -1;
    }

    .cms-field label {
        display: block;
        font-size: .8rem;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .cms-field-hint {
        font-size: .74rem;
        color: var(--muted);
        margin: 6px 0 0;
    }

    .cms-input,
    .cms-textarea {
        width: 100%;
        font-family: inherit;
        font-size: .9rem;
        color: var(--ink);
        padding: 10px 12px;
        border-radius: 9px;
        border: 1.5px solid var(--line-strong);
        background: var(--bg);
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .cms-textarea {
        min-height: 90px;
        resize: vertical;
        line-height: 1.5;
    }

    .cms-input:focus,
    .cms-textarea:focus {
        border-color: var(--pink);
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .12);
    }

    /* ---------- rich text editor ---------- */
    .richtext-wrap {
        border: 1.5px solid var(--line-strong);
        border-radius: 9px;
        overflow: hidden;
        background: var(--bg);
    }

    .richtext-wrap:focus-within {
        border-color: var(--pink);
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .12);
    }

    .richtext-toolbar {
        display: flex;
        align-items: center;
        gap: 3px;
        padding: 6px 7px;
        border-bottom: 1.5px solid var(--line-strong);
        background: var(--card);
        flex-wrap: wrap;
    }

    .richtext-btn {
        width: 28px;
        height: 28px;
        border: none;
        background: transparent;
        border-radius: 6px;
        color: var(--ink-soft);
        font-size: .8rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .richtext-btn:hover {
        background: var(--line);
        color: var(--ink);
    }

    .richtext-btn.italic i {
        font-style: italic;
    }

    .richtext-sep {
        width: 1px;
        height: 18px;
        background: var(--line-strong);
        margin: 0 3px;
    }

    .richtext-size,
    .richtext-font {
        font-family: inherit;
        font-size: .76rem;
        font-weight: 600;
        color: var(--ink);
        padding: 4px 7px;
        border-radius: 6px;
        border: 1px solid var(--line-strong);
        background: var(--bg);
        cursor: pointer;
    }

    .richtext-font {
        max-width: 112px;
    }

    .richtext-color-label {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        border: 1px solid var(--line-strong);
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
    }

    .richtext-color {
        width: 36px;
        height: 36px;
        border: none;
        padding: 0;
        margin: 0;
        cursor: pointer;
        background: none;
    }

    .richtext-editor {
        min-height: 110px;
        padding: 12px 13px;
        font-size: .95rem;
        line-height: 1.5;
        color: var(--ink);
        outline: none;
    }

    .cms-file-input {
        width: 100%;
        font-family: inherit;
        font-size: .82rem;
        padding: 10px 12px;
        border-radius: 9px;
        border: 1.5px dashed var(--line-strong);
        background: var(--bg);
        color: var(--ink-soft);
    }

    /* ---------- panel footer ---------- */
    .cms-panel-foot {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid var(--line);
    }

    .cms-lastsaved {
        font-size: .78rem;
        color: var(--muted);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .cms-foot-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* ---------- live preview section ---------- */
    .cms-preview-section {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .cms-preview-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .cms-live-badge {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: .78rem;
        font-weight: 800;
        color: var(--pink-dark);
        background: var(--pink-light);
        padding: 6px 14px;
        border-radius: 999px;
    }

    .cms-dot--pink {
        background: #fff;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .35);
    }

    .cms-viewport-label {
        font-weight: 700;
        color: var(--ink);
        margin-left: 4px;
        padding-left: 10px;
        border-left: 1px solid rgba(0, 0, 0, .12);
    }

    .cms-viewport-toggle {
        display: inline-flex;
        background: var(--card);
        border: 1.5px solid var(--line-strong);
        border-radius: 10px;
        padding: 3px;
        gap: 3px;
    }

    .cms-viewport-toggle button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: inherit;
        font-size: .76rem;
        font-weight: 700;
        padding: 7px 12px;
        border-radius: 7px;
        border: none;
        background: transparent;
        color: var(--ink-soft);
        cursor: pointer;
    }

    .cms-viewport-toggle button.is-active {
        background: var(--bg);
        color: var(--pink-dark);
    }

    /* ============================================================
       Section replicas — mirror public/css/landing.css 1:1 (colors,
       fonts, spacing) per section, scaled down for the preview
       frame. Every variable below matches the :root tokens in
       landing.css so colors never drift from the live site.
       ============================================================ */
    @import url('https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Inter:wght@400;600;700&family=Space+Mono:wght@700&display=swap');

    .cms-preview-frame {
        --wp-ink:         #221B36;
        --wp-ink-2:       #322A52;
        --wp-cream:       #FFF6E9;
        --wp-paper:       #FFFDF9;
        --wp-line:        rgba(34,27,54,.14);
        --wp-coral:       #FF5A5F;
        --wp-coral-dark:  #E5424A;
        --wp-amber:       #FFB627;
        --wp-amber-dark:  #DE9A00;
        --wp-teal:        #14B8A6;
        --wp-teal-dark:   #0D9488;
        --wp-violet:      #8B5CF6;
        --wp-violet-dark: #7347E0;

        width: 100%;
        border-radius: 16px;
        overflow: hidden;
        font-family: 'Inter', sans-serif;
        transition: max-width .2s ease;
    }

    .cms-preview-frame[data-viewport="mobile"] {
        max-width: 375px;
        margin: 0 auto;
    }

    .cms-btn-preview {
        display: inline-flex;
        align-items: center;
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        font-size: .76rem;
        padding: 8px 15px;
        border-radius: 9px;
        white-space: nowrap;
    }

    .cms-btn-preview--primary {
        background: var(--wp-coral);
        color: #fff;
    }

    .cms-btn-preview--outline {
        background: rgba(255, 255, 255, .08);
        border: 1.5px solid rgba(255, 255, 255, .55);
        color: #fff;
    }

    .cms-preview-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'Space Mono', monospace;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 5px 12px 5px 10px;
        border-radius: 999px;
        margin-bottom: 10px;
    }

    .cms-preview-pill--violet { background: rgba(139,92,246,.14); color: var(--wp-violet-dark); }
    .cms-preview-pill--coral  { background: rgba(255,90,95,.14);  color: var(--wp-coral-dark); }

    /* ---------- HERO replica (public: <section class="hero">) ---------- */
    .cms-preview-hero {
        position: relative;
        background: var(--wp-ink);
        aspect-ratio: 16 / 9;
    }

    .cms-preview-frame[data-viewport="mobile"] .cms-preview-hero {
        aspect-ratio: 9 / 16;
    }

    .cms-preview-bgvideo {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .55;
    }

    /* same two-layer overlay as .hero-video-overlay in landing.css */
    .cms-preview-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 1;
        background:
            radial-gradient(ellipse at 20% 20%, rgba(255, 90, 95, .28), transparent 55%),
            linear-gradient(180deg, rgba(34, 27, 54, .35) 0%, rgba(34, 27, 54, .78) 78%, rgba(34, 27, 54, .94) 100%);
        pointer-events: none;
    }

    .cms-preview-overlay {
        position: relative;
        z-index: 2;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 6%;
    }

    /* .ticket-label.on-dark equivalent */
    .cms-preview-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'Space Mono', monospace;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--wp-cream);
        background: rgba(255, 246, 233, .12);
        padding: 5px 12px 5px 10px;
        border-radius: 999px;
        width: fit-content;
        margin-bottom: 14px;
    }

    .cms-preview-tag::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        flex: none;
    }

    /* .hero-copy replica, incl. the amber marquee light-bulb strip */
    .cms-preview-copy {
        position: relative;
        max-width: 640px;
        padding: 20px 22px 18px;
    }

    .cms-preview-copy::before,
    .cms-preview-copy::after {
        content: '';
        position: absolute;
        left: 4px;
        right: 4px;
        height: 9px;
        background-image: radial-gradient(circle, var(--wp-amber) 2px, transparent 2.4px);
        background-size: 17px 9px;
        background-repeat: repeat-x;
        opacity: .85;
    }

    .cms-preview-copy::before { top: 0; }
    .cms-preview-copy::after { bottom: 0; }

    .cms-preview-headline {
        font-family: 'Baloo 2', sans-serif;
        font-weight: 800;
        font-size: clamp(1.3rem, 3vw, 2.5rem);
        line-height: 1.12;
        letter-spacing: -.01em;
        color: #fff;
        margin: 0;
        word-break: break-word;
    }

    /* matches .hero-copy h1 .accent on the live landing page */
    .cms-preview-headline .accent {
        color: var(--wp-amber);
    }

    .cms-preview-desc {
        font-family: 'Inter', sans-serif;
        font-size: .82rem;
        line-height: 1.65;
        color: rgba(255, 246, 233, .86);
        margin: 14px 0 18px;
        max-width: 54ch;
        word-break: break-word;
    }

    .cms-preview-desc .cms-preview-price {
        font-weight: 700;
        color: var(--wp-amber);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .cms-preview-cta {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .cms-preview-stamps {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 18px;
    }

    .cms-preview-stamp {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'Space Mono', monospace;
        font-size: .66rem;
        font-weight: 700;
        letter-spacing: .03em;
        color: rgba(255, 246, 233, .92);
        background: rgba(255, 246, 233, .08);
        border: 1px solid rgba(255, 246, 233, .22);
        padding: 5px 10px 5px 8px;
        border-radius: 999px;
    }

    .cms-preview-stamp .swatch {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        flex: none;
    }

    /* .hero-video-meta replica — bottom-right badge + sound icon */
    .cms-preview-videobar {
        position: absolute;
        right: 5%;
        bottom: 4%;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cms-preview-video-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-family: 'Space Mono', monospace;
        font-size: .68rem;
        color: rgba(255, 246, 233, .85);
        background: rgba(0, 0, 0, .28);
        border: 1px solid rgba(255, 255, 255, .2);
        padding: 6px 11px;
        border-radius: 999px;
    }

    .cms-preview-video-badge .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--wp-coral);
        box-shadow: 0 0 0 3px rgba(255, 90, 95, .25);
    }

    .cms-preview-videobar-right {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: rgba(0, 0, 0, .28);
        border: 1px solid rgba(255, 255, 255, .2);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .72rem;
    }

    /* ---------- SPLIT replica (public: .split-section) ---------- */
    .cms-preview-split {
        background: var(--wp-cream);
        padding: 6%;
    }

    .cms-preview-split-grid {
        display: grid;
        grid-template-columns: .95fr 1.05fr;
        gap: 5%;
        align-items: center;
    }

    .cms-preview-frame[data-viewport="mobile"] .cms-preview-split-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .cms-preview-split-visual {
        position: relative;
        border-radius: 14px;
        overflow: hidden;
        min-height: 170px;
        background: var(--wp-ink);
    }

    .cms-preview-split-video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cms-preview-split-visual::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 55%, rgba(34, 27, 54, .55));
    }

    .cms-preview-split-tag {
        position: absolute;
        left: 12px;
        bottom: 12px;
        z-index: 2;
        font-family: 'Space Mono', monospace;
        color: #fff;
        font-size: .64rem;
        font-weight: 700;
        letter-spacing: .04em;
    }

    .cms-preview-split-title {
        font-family: 'Baloo 2', sans-serif;
        font-weight: 800;
        color: var(--wp-ink);
        font-size: clamp(1.15rem, 2.4vw, 1.7rem);
        margin: 6px 0 10px;
        line-height: 1.18;
    }

    .cms-preview-split-desc {
        color: var(--wp-ink-2);
        opacity: .82;
        font-size: .82rem;
        line-height: 1.6;
        margin-bottom: 14px;
    }

    .cms-preview-split-list {
        list-style: none;
        margin: 0 0 16px;
        padding: 0;
    }

    .cms-preview-split-list li {
        display: flex;
        gap: 8px;
        align-items: flex-start;
        font-size: .78rem;
        color: var(--wp-ink-2);
        padding: 3px 0;
        line-height: 1.4;
    }

    .cms-preview-split-list li::before {
        content: '★';
        color: var(--wp-amber-dark);
        flex: none;
        margin-top: 1px;
    }

    /* ---------- CONTACT replica (public: #contact .contact) ---------- */
    .cms-preview-contact-wrap {
        background: var(--wp-cream);
        padding: 6%;
    }

    .cms-preview-contact {
        background: var(--wp-paper);
        border: 1px solid var(--wp-line);
        border-radius: 16px;
        padding: 5%;
        display: grid;
        grid-template-columns: 1.1fr .9fr;
        gap: 6%;
    }

    .cms-preview-frame[data-viewport="mobile"] .cms-preview-contact {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .cms-preview-contact-title {
        font-family: 'Baloo 2', sans-serif;
        font-weight: 800;
        color: var(--wp-ink);
        font-size: clamp(1.15rem, 2.4vw, 1.7rem);
        margin: 8px 0 10px;
    }

    .cms-preview-contact-desc {
        color: var(--wp-ink-2);
        opacity: .8;
        font-size: .82rem;
        line-height: 1.6;
        margin-bottom: 16px;
    }

    .cms-preview-info-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 9px 0;
        border-top: 1px dashed var(--wp-line);
    }

    .cms-preview-info-row:first-child {
        border-top: none;
    }

    .cms-preview-info-row b {
        font-family: 'Space Mono', monospace;
        font-size: .62rem;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--wp-coral-dark);
    }

    .cms-preview-info-row span {
        font-size: .8rem;
        color: var(--wp-ink);
    }

    /* ---------- FOOTER replica (public: <footer>) ---------- */
    .cms-preview-footer {
        background: var(--wp-ink);
        color: rgba(255, 246, 233, .75);
        padding: 8%;
    }

    .cms-preview-footer-title {
        font-family: 'Baloo 2', sans-serif;
        font-weight: 800;
        color: #fff;
        font-size: clamp(1.2rem, 2.6vw, 1.6rem);
        margin-bottom: 10px;
    }

    .cms-preview-footer-tagline {
        max-width: 52ch;
        line-height: 1.6;
        font-size: .8rem;
    }

    .cms-preview-footer-zones {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin: 16px 0 14px;
    }

    .cms-preview-footer-zones span {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: .74rem;
        font-weight: 600;
        color: rgba(255, 246, 233, .85);
    }

    .cms-preview-footer-zones .swatch {
        width: 7px;
        height: 7px;
        border-radius: 50%;
    }

    .cms-preview-footer-copy {
        border-top: 1px dashed rgba(255, 246, 233, .2);
        padding-top: 12px;
        margin-top: 12px;
        font-size: .68rem;
        color: rgba(255, 246, 233, .5);
    }

    /* ---------- generic fallback (unmapped sections) ---------- */
    .cms-preview-generic {
        background: var(--wp-cream);
        padding: 6%;
        color: var(--wp-ink);
        font-size: .85rem;
    }

    .cms-preview-generic p {
        color: var(--wp-ink-2);
        opacity: .8;
        margin-bottom: 10px;
    }

    .cms-preview-generic ul {
        list-style: none;
        padding: 0;
        margin: 12px 0 0;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .cms-preview-generic li {
        border-bottom: 1px dashed var(--wp-line);
        padding-bottom: 8px;
    }

    .cms-preview-generic li b {
        display: block;
        font-family: 'Space Mono', monospace;
        font-size: .66rem;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--wp-coral-dark);
        margin-bottom: 3px;
    }

    .cms-preview-footnote {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        font-size: .76rem;
        color: var(--muted);
    }

    @media (max-width: 900px) {
        .cms-top {
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
    @php
        $defaultVideo = asset('videos/landing.mp4');
        $sectionKey = $section;

        // Per-section field-key -> preview-slot mapping. These are the
        // exact array keys resources/views/landing.blade.php reads for
        // each section ($hero['title'], $split['location_tag'],
        // $contact['hours_weekend'], $footer['tagline'], etc). If the
        // actual field keys configured for a section differ from this
        // list, update the map below to match — that's the single
        // source of truth this preview binds to.
        $keyMap = [
            'hero'    => ['title', 'description', 'video_path', 'price_teaser'],
            'split'   => ['tag', 'title', 'description', 'location_tag'],
            'contact' => ['location', 'hours_weekend', 'hours_weekday', 'zones'],
            'footer'  => ['tagline'],
        ];

        $sectionFieldKeys = $keyMap[$sectionKey] ?? [];

        // $has('key') / $fkey('key') / $pv('key', $fallback) all key off
        // the *actual* field array ($fields) so a field only binds to the
        // preview if this section really defines it — otherwise the
        // fallback (the same copy landing.blade.php falls back to) shows.
        $has = function ($key) use ($fields) {
            return array_key_exists($key, $fields);
        };
        $fkey = function ($key) {
            return $key;
        };
        $pv = function ($key, $fallback = '') use ($fields, $values) {
            if (array_key_exists($key, $fields)) {
                $val = $values[$key] ?? null;
                if ($val !== null && $val !== '') return $val;
            }
            return $fallback;
        };
    @endphp

    <div class="cms-page">

        <div class="cms-crumb">
            <span>{{ config('app.name', 'WonderPark') }} Lipa Branch</span>
            <i class="fa-solid fa-chevron-right"></i>
            <a href="{{ route('cms.index') }}">Website Management</a>
            <i class="fa-solid fa-chevron-right"></i>
            <span class="is-current">{{ ucfirst($section) }}</span>
        </div>

        <div class="cms-top">
            <div>
                <h1>{{ ucfirst($section) }} CMS</h1>
                <p>Edit the content shown in this part of the site. Changes appear in the live preview below.</p>
            </div>
            <div class="cms-top-actions">
                <button type="button" class="cms-btn-ghost" id="scrollToPreviewTop">
                    <i class="fa-regular fa-eye"></i> Live Preview
                </button>
                <button type="submit" form="cmsSectionForm" class="cms-btn-solid">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="cms-alert cms-alert--success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="cms-alert cms-alert--error">There are errors in the form — please check the fields.</div>
        @endif

        <div class="cms-panel">
            <div class="cms-panel-head">
                <span class="cms-panel-title"><span class="cms-dot"></span> {{ strtoupper($section) }} CONTENT CONFIGURATION</span>
                <span class="cms-panel-hint">Changes appear in the live preview below</span>
            </div>

            <form action="{{ route('cms.section.update', $section) }}" method="POST" enctype="multipart/form-data" id="cmsSectionForm">
                @csrf
                @method('PUT')

                <div class="cms-fields-grid">
                    @foreach ($fields as $key => $meta)
                        @php $type = $meta['type'] ?? 'text'; @endphp

                        <div class="cms-field {{ in_array($type, ['richtext', 'textarea']) ? 'cms-field-span' : '' }}">
                            <label for="{{ $key }}">{{ $meta['label'] }}</label>

                            @if ($type === 'video')
                                <input type="file" name="{{ $key }}" id="{{ $key }}" accept="video/mp4,video/webm,video/quicktime" class="cms-file-input" data-video-input="{{ $key }}">
                                <p class="cms-field-hint">MP4, WebM, or MOV. Max 50MB. Leave blank to keep the current video.</p>
                            @elseif ($type === 'richtext')
                                <div class="richtext-wrap" data-richtext-wrap>
                                    <div class="richtext-toolbar">
                                        <button type="button" class="richtext-btn" data-cmd="bold" title="Bold"><i class="fa-solid fa-bold"></i></button>
                                        <button type="button" class="richtext-btn italic" data-cmd="italic" title="Italic"><i class="fa-solid fa-italic"></i></button>
                                        <button type="button" class="richtext-btn" data-cmd="underline" title="Underline"><i class="fa-solid fa-underline"></i></button>
                                        <div class="richtext-sep"></div>
                                        <select class="richtext-font" data-fontfamily title="Font style">
                                            <option value="">Font</option>
                                            <option value="Arial, sans-serif">Arial</option>
                                            <option value="Georgia, serif">Georgia</option>
                                            <option value="'Times New Roman', serif">Times New Roman</option>
                                            <option value="'Courier New', monospace">Courier New</option>
                                            <option value="Verdana, sans-serif">Verdana</option>
                                            <option value="'Trebuchet MS', sans-serif">Trebuchet MS</option>
                                        </select>
                                        <select class="richtext-size" data-fontsize title="Font size">
                                            <option value="">Size</option>
                                            <option value="14px">Small</option>
                                            <option value="18px" selected>Normal</option>
                                            <option value="24px">Large</option>
                                            <option value="32px">X-Large</option>
                                            <option value="44px">Huge</option>
                                        </select>
                                        <div class="richtext-sep"></div>
                                        <label class="richtext-color-label" title="Font color">
                                            <input type="color" class="richtext-color" data-fontcolor value="#1a1a1a">
                                        </label>
                                    </div>
                                    <div class="richtext-editor" contenteditable="true" data-richtext-editor id="{{ $key }}_editor">{!! old($key, $values[$key] ?? '') !!}</div>
                                </div>
                                <input type="hidden" name="{{ $key }}" id="{{ $key }}" value="{{ old($key, $values[$key] ?? '') }}" data-richtext-input>
                                <p class="cms-field-hint">Select text, then use the toolbar for bold, italic, underline, font, size, or color.</p>
                            @elseif ($type === 'textarea')
                                <textarea name="{{ $key }}" id="{{ $key }}" class="cms-textarea" data-plain-field="{{ $key }}">{{ old($key, $values[$key] ?? '') }}</textarea>
                            @else
                                <input type="text" name="{{ $key }}" id="{{ $key }}" class="cms-input" value="{{ old($key, $values[$key] ?? '') }}" data-plain-field="{{ $key }}">
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="cms-panel-foot">
                    @isset($lastSaved)
                        <span class="cms-lastsaved"><i class="fa-regular fa-clock"></i> Last saved: {{ $lastSaved }}</span>
                    @else
                        <span></span>
                    @endisset
                    <div class="cms-foot-actions">
                        <button type="button" class="cms-btn-ghost" id="scrollToPreviewBottom">
                            <i class="fa-solid fa-arrow-down"></i> Scroll to Preview
                        </button>
                        <button type="submit" class="cms-btn-solid">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="cms-preview-section" id="livePreviewSection">
            <div class="cms-preview-topbar">
                <span class="cms-live-badge">
                    <span class="cms-dot cms-dot--pink"></span> LIVE PREVIEW
                    <span class="cms-viewport-label">Customer Facing Viewport</span>
                </span>
                <div class="cms-viewport-toggle">
                    <button type="button" class="is-active" data-viewport="desktop"><i class="fa-solid fa-desktop"></i> Desktop</button>
                    <button type="button" data-viewport="mobile"><i class="fa-solid fa-mobile-screen-button"></i> Mobile</button>
                </div>
            </div>

            <div class="cms-preview-frame" id="previewCanvas" data-viewport="desktop">

                @if ($sectionKey === 'hero')
                    {{-- mirrors <section class="hero"> in landing.blade.php --}}
                    <div class="cms-preview-hero">
                        <video class="cms-preview-bgvideo"
                            @if ($has('video_path')) data-preview-target="{{ $fkey('video_path') }}" @endif
                            autoplay muted loop playsinline
                            src="{{ $pv('video_path', $defaultVideo) }}"></video>

                        <div class="cms-preview-overlay">
                            <span class="cms-preview-tag">Field of Rides • Open Daily</span>

                            <div class="cms-preview-copy">
                                <h2 class="cms-preview-headline" @if ($has('title')) data-preview-target="{{ $fkey('title') }}" @endif>{!! $pv('title', 'Three worlds of thrill,<br><span class="accent">one ticket away.</span>') !!}</h2>

                                <p class="cms-preview-desc">
                                    <span @if ($has('description')) data-preview-target="{{ $fkey('description') }}" @endif>{{ $pv('description', "From the gravity-defying loops of Roller Fever to the prehistoric trails of Dino Adventure, Wonder Park is built for a full day of family fun — now bookable, trackable, and paid for online.") }}</span>
                                    Passes start as low as
                                    <span class="cms-preview-price" @if ($has('price_teaser')) data-preview-target="{{ $fkey('price_teaser') }}" @endif>{{ $pv('price_teaser', '₱149') }}</span>
                                    — tap the price to book your slot.
                                </p>

                                <div class="cms-preview-cta">
                                    <span class="cms-btn-preview cms-btn-preview--primary">Reserve a Slot</span>
                                    <span class="cms-btn-preview cms-btn-preview--outline">See Attractions</span>
                                </div>

                                <div class="cms-preview-stamps">
                                    <span class="cms-preview-stamp"><span class="swatch" style="background:#FF5A5F"></span>Field of Rides</span>
                                    <span class="cms-preview-stamp"><span class="swatch" style="background:#14B8A6"></span>Roller Fever</span>
                                    <span class="cms-preview-stamp"><span class="swatch" style="background:#FFB627"></span>Dino Adventure</span>
                                </div>
                            </div>
                        </div>

                        <div class="cms-preview-videobar">
                            <span class="cms-preview-video-badge"><span class="dot"></span>At Wonder Park</span>
                            <div class="cms-preview-videobar-right"><i class="fa-solid fa-volume-high"></i></div>
                        </div>
                    </div>

                @elseif ($sectionKey === 'split')
                    {{-- mirrors <section class="split-section"> in landing.blade.php --}}
                    <div class="cms-preview-split">
                        <div class="cms-preview-split-grid">
                            <div class="cms-preview-split-visual">
                                <video class="cms-preview-split-video" autoplay muted loop playsinline src="{{ $defaultVideo }}"></video>
                                <span class="cms-preview-split-tag" @if ($has('location_tag')) data-preview-target="{{ $fkey('location_tag') }}" @endif>{{ $pv('location_tag', 'Lima Technology Center · Lipa City / Malvar') }}</span>
                            </div>
                            <div>
                                <span class="cms-preview-pill cms-preview-pill--violet" @if ($has('tag')) data-preview-target="{{ $fkey('tag') }}" @endif>{{ $pv('tag', 'Plan Your Day') }}</span>
                                <h2 class="cms-preview-split-title" @if ($has('title')) data-preview-target="{{ $fkey('title') }}" @endif>{{ $pv('title', 'Make it a full day out — not just a stop-by') }}</h2>
                                <p class="cms-preview-split-desc" @if ($has('description')) data-preview-target="{{ $fkey('description') }}" @endif>{{ $pv('description', "Wonder Park sits inside the Lima Technology Center complex, so a booking here pairs easily with a longer family day: grab a meal nearby, then swing through all three zones before closing.") }}</p>
                                <ul class="cms-preview-split-list">
                                    <li>Open daily, including holidays — no need to plan around a rest day</li>
                                    <li>Weekend hours run longer (10AM–9PM) for full-day visits</li>
                                    <li>Group bookings and school field trips are coordinated in advance</li>
                                    <li>Walk-ins welcome across all three zones, subject to capacity</li>
                                </ul>
                                <span class="cms-btn-preview cms-btn-preview--primary">Choose a Pass</span>
                            </div>
                        </div>
                    </div>

                @elseif ($sectionKey === 'contact')
                    {{-- mirrors <section id="contact"> in landing.blade.php --}}
                    <div class="cms-preview-contact-wrap">
                        <div class="cms-preview-contact">
                            <div>
                                <span class="cms-preview-pill cms-preview-pill--coral">Visit Us</span>
                                <h2 class="cms-preview-contact-title">Plan your visit to Wonder Park</h2>
                                <p class="cms-preview-contact-desc">Open daily, including holidays. Group bookings and school field trips are coordinated in advance through our reservations team.</p>
                                <span class="cms-btn-preview cms-btn-preview--primary">Book a Slot</span>
                            </div>
                            <div>
                                <div class="cms-preview-info-row">
                                    <b>Location</b>
                                    <span @if ($has('location')) data-preview-target="{{ $fkey('location') }}" @endif>{{ $pv('location', 'Lima Technology Center, Lipa City/Malvar, Batangas') }}</span>
                                </div>
                                <div class="cms-preview-info-row">
                                    <b>Hours</b>
                                    <span @if ($has('hours_weekend')) data-preview-target="{{ $fkey('hours_weekend') }}" @endif>{{ $pv('hours_weekend', '10:00 AM – 9:00 PM Weekends') }}</span>
                                    <span @if ($has('hours_weekday')) data-preview-target="{{ $fkey('hours_weekday') }}" @endif>{{ $pv('hours_weekday', '11:00 AM – 9:00 PM Weekdays') }}</span>
                                </div>
                                <div class="cms-preview-info-row">
                                    <b>Zones</b>
                                    <span @if ($has('zones')) data-preview-target="{{ $fkey('zones') }}" @endif>{{ $pv('zones', 'Field of Rides · Roller Fever · Dino Adventure') }}</span>
                                </div>
                                <div class="cms-preview-info-row">
                                    <b>Bookings</b>
                                    <span>Online reservation or walk-in (subject to capacity)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif ($sectionKey === 'footer')
                    {{-- mirrors <footer> in landing.blade.php --}}
                    <div class="cms-preview-footer">
                        <h3 class="cms-preview-footer-title">Wonder Park</h3>
                        <p class="cms-preview-footer-tagline" @if ($has('tagline')) data-preview-target="{{ $fkey('tagline') }}" @endif>{{ $pv('tagline', "From thrilling rides to prehistoric adventures and endless skating fun, Wonder Park is your destination for unforgettable family experiences.") }}</p>
                        <div class="cms-preview-footer-zones">
                            <span><span class="swatch" style="background:#FF5A5F"></span>Field of Rides</span>
                            <span><span class="swatch" style="background:#14B8A6"></span>Roller Fever</span>
                            <span><span class="swatch" style="background:#FFB627"></span>Dino Adventure</span>
                        </div>
                        <p class="cms-preview-footer-copy">© {{ date('Y') }} Wonder Park. All Rights Reserved.</p>
                    </div>

                @else
                    {{-- no dedicated layout mapped yet for this section --}}
                    <div class="cms-preview-generic">
                        <p>No dedicated live-preview layout for the "{{ $section }}" section yet — showing the raw field values below instead of a styled mismatch.</p>
                        <ul>
                            @foreach ($fields as $gkey => $gmeta)
                                <li>
                                    <b>{{ $gmeta['label'] ?? $gkey }}</b>
                                    <span data-preview-target="{{ $gkey }}">{{ is_string($values[$gkey] ?? null) ? strip_tags($values[$gkey]) : '' }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

            </div>

            <div class="cms-preview-footnote">
                <span>✨ Real-time preview synchronized with form fields above.</span>
                <span>Target: Public Landing Page — {{ ucfirst($section) }} Section</span>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function() {
    // Rich text toolbar behaviour + live preview sync (richtext fields only)
    document.querySelectorAll('[data-richtext-wrap]').forEach(function(wrap) {
        const editor = wrap.querySelector('[data-richtext-editor]');
        const hiddenInput = wrap.parentElement.querySelector('[data-richtext-input]');
        const sizeSelect = wrap.querySelector('[data-fontsize]');
        const fontSelect = wrap.querySelector('[data-fontfamily]');
        const colorInput = wrap.querySelector('[data-fontcolor]');
        const previewTarget = hiddenInput ? document.querySelector('[data-preview-target="' + hiddenInput.id + '"]') : null;

        function syncToInput() {
            if (hiddenInput) hiddenInput.value = editor.innerHTML;
            if (previewTarget) previewTarget.innerHTML = editor.innerHTML;
        }

        wrap.querySelectorAll('[data-cmd]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                editor.focus();
                document.execCommand(btn.dataset.cmd, false, null);
                syncToInput();
            });
        });

        if (fontSelect) {
            fontSelect.addEventListener('change', function() {
                if (!this.value) return;
                editor.focus();
                document.execCommand('fontName', false, this.value);
                this.value = '';
                syncToInput();
            });
        }

        if (sizeSelect) {
            sizeSelect.addEventListener('change', function() {
                if (!this.value) return;
                editor.focus();

                // execCommand fontSize only accepts 1-7, so use a marker
                // size (7) then swap the resulting <font size="7"> tags
                // for a <span style="font-size:Xpx"> with the real value.
                document.execCommand('fontSize', false, '7');
                editor.querySelectorAll('font[size="7"]').forEach(function(f) {
                    const span = document.createElement('span');
                    span.style.fontSize = sizeSelect.value;
                    span.innerHTML = f.innerHTML;
                    f.replaceWith(span);
                });

                this.value = '';
                syncToInput();
            });
        }

        if (colorInput) {
            colorInput.addEventListener('input', function() {
                editor.focus();
                document.execCommand('foreColor', false, this.value);
                syncToInput();
            });
        }

        editor.addEventListener('input', syncToInput);
        editor.addEventListener('blur', syncToInput);
    });

    // Plain text/textarea fields -> live preview sync
    document.querySelectorAll('[data-plain-field]').forEach(function(field) {
        const previewTarget = document.querySelector('[data-preview-target="' + field.id + '"]');
        if (!previewTarget) return;

        field.addEventListener('input', function() {
            previewTarget.textContent = field.value;
        });
    });

    // Video upload live preview
    document.querySelectorAll('[data-video-input]').forEach(function(input) {
        const key = input.dataset.videoInput;
        const videoTarget = document.querySelector('video[data-preview-target="' + key + '"]');

        input.addEventListener('change', function() {
            if (this.files && this.files[0] && videoTarget) {
                videoTarget.src = URL.createObjectURL(this.files[0]);
            }
        });
    });

    // Belt-and-suspenders: make sure rich-text hidden inputs are current
    // right before the form actually submits.
    const form = document.getElementById('cmsSectionForm');
    if (form) {
        form.addEventListener('submit', function() {
            document.querySelectorAll('[data-richtext-wrap]').forEach(function(wrap) {
                const editor = wrap.querySelector('[data-richtext-editor]');
                const hiddenInput = wrap.parentElement.querySelector('[data-richtext-input]');
                if (hiddenInput) hiddenInput.value = editor.innerHTML;
            });
        });
    }

    // Desktop / Mobile viewport toggle for the live preview
    const canvas = document.getElementById('previewCanvas');
    document.querySelectorAll('.cms-viewport-toggle button').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.cms-viewport-toggle button').forEach(function(b) {
                b.classList.toggle('is-active', b === btn);
            });
            if (canvas) canvas.dataset.viewport = btn.dataset.viewport;
        });
    });

    // Scroll-to-preview buttons
    ['scrollToPreviewTop', 'scrollToPreviewBottom'].forEach(function(id) {
        const btn = document.getElementById(id);
        const target = document.getElementById('livePreviewSection');
        if (btn && target) {
            btn.addEventListener('click', function() {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    });
})();
</script>
@endpush