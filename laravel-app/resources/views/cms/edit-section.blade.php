@extends('layouts.sidebar')

@section('title', ucfirst($section) . ' Section')

@section('styles')
<style>
    /* ============================================================
       Minimal, flat CMS section editor.
       One accent color, one border weight, no stacked shadows or
       decorative gradients — the layout should stay out of the way
       of reading fields and checking the preview.
       ============================================================ */

    .cms-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .82rem;
        font-weight: 600;
        color: var(--ink-soft);
        text-decoration: none;
    }

    .cms-back:hover {
        color: var(--pink-dark);
    }

    .cms-header {
        margin: 10px 0 28px;
    }

    .cms-header h1 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--ink);
        margin: 4px 0 6px;
    }

    .cms-header p {
        font-size: .88rem;
        color: var(--muted);
        margin: 0;
        max-width: 60ch;
    }

    .cms-alert {
        margin-bottom: 20px;
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

    .cms-section-shell {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 20px;
        align-items: start;
    }

    @media (max-width: 900px) {
        .cms-section-shell {
            grid-template-columns: 1fr;
        }

        .cms-section-side {
            position: static;
        }
    }

    .cms-form-wrap {
        background: var(--card);
        border-radius: 14px;
        padding: 28px;
        border: 1px solid var(--line);
    }

    .cms-fields-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px 18px;
    }

    @media (max-width: 560px) {
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
        font-weight: 600;
        color: var(--ink);
        margin-bottom: 6px;
    }

    .cms-field-hint {
        font-size: .74rem;
        color: var(--muted);
        margin: 6px 0 0;
    }

    /* --- Plain inputs, used for simple one-line and multi-line fields --- */
    .cms-input,
    .cms-textarea {
        width: 100%;
        font-family: inherit;
        font-size: .92rem;
        color: var(--ink);
        padding: 10px 12px;
        border-radius: 9px;
        border: 1.5px solid var(--line-strong);
        background: var(--bg);
        outline: none;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .cms-textarea {
        min-height: 96px;
        resize: vertical;
        line-height: 1.5;
    }

    .cms-input:focus,
    .cms-textarea:focus {
        border-color: var(--pink);
        box-shadow: 0 0 0 3px rgba(255, 92, 133, .12);
    }

    .cms-submit {
        margin-top: 26px;
        padding: 11px 24px;
        border-radius: 9px;
        background: var(--pink);
        color: #fff;
        font-weight: 700;
        font-size: .88rem;
        border: none;
        cursor: pointer;
        transition: background .15s ease;
    }

    .cms-submit:hover {
        background: var(--pink-dark);
    }

    /* --- Rich text editor — reserved for fields actually configured
       as 'richtext' (e.g. a hero headline that needs bold/italic) --- */
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
        min-height: 120px;
        padding: 12px 13px;
        font-size: .95rem;
        line-height: 1.5;
        color: var(--ink);
        outline: none;
    }

    /* --- Video upload --- */
    .cms-file-input {
        width: 100%;
        font-family: inherit;
        font-size: .84rem;
        padding: 10px 12px;
        border-radius: 9px;
        border: 1.5px dashed var(--line-strong);
        background: var(--bg);
        color: var(--ink-soft);
    }

    /* --- Live preview sidebar --- */
    .cms-section-side {
        position: sticky;
        top: 20px;
    }

    .cms-preview-panel {
        background: var(--card);
        border-radius: 14px;
        border: 1px solid var(--line);
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .cms-preview-panel-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: .78rem;
        font-weight: 600;
        color: var(--ink-soft);
        padding-bottom: 14px;
        border-bottom: 1px solid var(--line);
    }

    .cms-preview-panel-label::before {
        content: '';
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: var(--pink);
        flex-shrink: 0;
    }

    .cms-preview-block {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .cms-preview-label {
        font-size: .68rem;
        font-weight: 600;
        color: var(--muted);
    }

    .cms-preview-text {
        font-size: .88rem;
        color: var(--ink);
        line-height: 1.5;
        word-break: break-word;
    }

    .cms-preview-video-wrap video {
        width: 100%;
        border-radius: 9px;
        background: #000;
        display: block;
    }

    .cms-preview-empty {
        font-size: .8rem;
        color: var(--muted);
        font-style: italic;
    }
</style>
@endsection

@section('content')
    <a href="{{ route('cms.index') }}" class="cms-back"><i class="fa-solid fa-arrow-left"></i> Back to CMS</a>

    <div class="cms-header">
        <h1>{{ ucfirst($section) }} section</h1>
        <p>Edit the content shown in this part of the site. Changes appear in the preview as you type, and go live once you save.</p>
    </div>

    @if (session('success'))
        <div class="cms-alert cms-alert--success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="cms-alert cms-alert--error">There are errors in the form — please check the fields.</div>
    @endif

    <div class="cms-section-shell">
        <div class="cms-form-wrap">
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

                <button type="submit" class="cms-submit">Save changes</button>
            </form>
        </div>

        <div class="cms-section-side">
            <div class="cms-preview-panel">
                <div class="cms-preview-panel-label">Live preview</div>

                @foreach ($fields as $key => $meta)
                    @php $type = $meta['type'] ?? 'text'; @endphp

                    <div class="cms-preview-block">
                        <span class="cms-preview-label">{{ $meta['label'] }}</span>

                        @if ($type === 'video')
                            <div class="cms-preview-video-wrap">
                                <video data-preview-target="{{ $key }}" controls muted
                                    @if (!empty($values[$key] ?? null)) src="{{ $values[$key] }}" @else style="display:none;" @endif></video>
                                <p class="cms-preview-empty" data-preview-empty="{{ $key }}" @if (!empty($values[$key] ?? null)) style="display:none;" @endif>No video uploaded yet</p>
                            </div>
                        @elseif ($type === 'richtext')
                            <div class="cms-preview-text" data-preview-target="{{ $key }}">{!! $values[$key] ?? '' !!}</div>
                        @else
                            <div class="cms-preview-text" data-preview-target="{{ $key }}">{{ $values[$key] ?? '' }}</div>
                        @endif
                    </div>
                @endforeach
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
        const emptyTarget = document.querySelector('[data-preview-empty="' + key + '"]');

        input.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const url = URL.createObjectURL(this.files[0]);
                if (videoTarget) {
                    videoTarget.src = url;
                    videoTarget.style.display = '';
                }
                if (emptyTarget) emptyTarget.style.display = 'none';
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
})();
</script>
@endpush