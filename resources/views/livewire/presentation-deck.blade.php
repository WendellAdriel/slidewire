<div
    x-ref="deckRoot"
    x-data="slidewireDeck(
        $wire,
        @entangle('activeIndex').live,
        @entangle('activeFragment').live,
        {{ count($slideFrames) }},
        @js($slideThemes),
        @js($configuredThemes),
        @js($defaultTheme),
        @js($deckPayload['transition_durations']),
        @js($deckPayload['auto_slides']),
        @js((bool) ($deckMeta['auto_slide_pause_on_interaction'] ?? $slidesConfig->autoSlidePauseOnInteraction)),
        @js($gridShape),
        @js($deckPayload['coords']),
        @js($themeTypography)
    )"
    x-bind:class="currentThemeClass()"
    x-on:keydown.window.right="onArrowKey($event, 'right')"
    x-on:keydown.window.left="onArrowKey($event, 'left')"
    x-on:keydown.window.down="onArrowKey($event, 'down')"
    x-on:keydown.window.up="onArrowKey($event, 'up')"
    x-on:keydown.window.prevent.space="next()"
    class="slidewire-shell"
>
    @if($googleFontsUrl)
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="{{ $googleFontsUrl }}" rel="stylesheet">
    @endif

    <div class="slidewire-stage" x-on:click="next()">
        @if($showProgress)
            <div class="slidewire-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"
                :aria-valuenow="Math.round(((index + 1) / count) * 100)">
                <div class="slidewire-progress-bar" :style="`width: ${((index + 1) / count) * 100}%`"></div>
            </div>
        @endif

        @foreach ($slideFrames as $slideIndex => $frame)
            @php
                $slide = $frame['slide'];
                $meta = $slide->meta;
                $effective = $slide->effective;
                $frameBackgroundImage = $frame['background_image'];
                $frameBackgroundVideo = $frame['background_video'];
                $frameStyle = $frame['style'];
                $frameTextTypography = $frame['text_typography'];
                $frameVideoMuted = $frame['video_muted'];
                $frameVideoLoops = $frame['video_loops'];
            @endphp

            <section
                x-bind:class="frameClass({{ $slideIndex }})"
                x-ref="slide{{ $slideIndex }}"
                wire:key="slide-{{ $slide->id }}"
                class="slidewire-frame {{ $slide->class }} {{ $frame['slide_theme_class'] }}"
                data-transition="{{ $effective['transition'] ?? $slidesConfig->transition->value }}"
                data-transition-speed="{{ $effective['transition_speed'] ?? $slidesConfig->transitionSpeed->value }}"
                data-auto-animate="{{ $meta['auto_animate'] ?? $deckMeta['auto_animate'] ?? 'false' }}"
                data-auto-animate-duration="{{ $meta['auto_animate_duration'] ?? $deckMeta['auto_animate_duration'] ?? '420' }}"
                data-auto-animate-easing="{{ $meta['auto_animate_easing'] ?? $deckMeta['auto_animate_easing'] ?? 'ease' }}"
                data-auto-slide="{{ $effective['auto_slide'] ?? '' }}"
                data-theme="{{ $effective['theme'] ?? '' }}"
                data-h="{{ $slide->h }}"
                data-v="{{ $slide->v }}"
                @if(isset($meta['background_transition']))
                    data-background-transition="{{ $meta['background_transition'] }}"
                @endif
                @if($frameBackgroundImage)
                    data-background-image="{{ $frameBackgroundImage }}"
                @endif
                @if($frameBackgroundVideo)
                    data-background-video="{{ $frameBackgroundVideo }}"
                @endif
                style="{{ $frameStyle }}"
            >
                @if($frameBackgroundVideo)
                    <video class="slidewire-background-media" autoplay playsinline @if($frameVideoMuted) muted @endif @if($frameVideoLoops) loop @endif>
                        <source src="{{ $frameBackgroundVideo }}" />
                    </video>
                @endif

                <div class="slidewire-content {{ $frameTextTypography }}">
                    {!! $slide->html !!}
                </div>
            </section>
        @endforeach

        @if($showControls)
            <nav class="slidewire-controls" aria-label="Slide controls">
                <button type="button" x-on:click.stop="previous()" aria-label="Previous slide" class="slidewire-control-arrow slidewire-control-left" :disabled="!canGoLeft()">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                </button>
                @if($hasVerticalSlides)
                    <button type="button" x-on:click.stop="navigateUp()" aria-label="Slide up" class="slidewire-control-arrow slidewire-control-up" :disabled="!canGoUp()">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"/></svg>
                    </button>
                    <button type="button" x-on:click.stop="navigateDown()" aria-label="Slide down" class="slidewire-control-arrow slidewire-control-down" :disabled="!canGoDown()">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    </button>
                @endif
                <button type="button" x-on:click.stop="next()" aria-label="Next slide" class="slidewire-control-arrow slidewire-control-right" :disabled="!canGoRight()">
                    <svg viewBox="0 0 20 20" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </button>
                @if($showFullscreenButton)
                    <button
                        type="button"
                        x-on:click.stop="toggleFullscreen()"
                        x-bind:aria-label="isFullscreen ? 'Exit fullscreen' : 'Enter fullscreen'"
                    >
                        <svg x-show="!isFullscreen" x-cloak viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
                            <path fill-rule="evenodd" d="M3 7a1 1 0 011-1h2a1 1 0 100-2H4a3 3 0 00-3 3v2a1 1 0 102 0V7zm13-3a1 1 0 100 2h2v2a1 1 0 102 0V7a3 3 0 00-3-3h-2zM3 13a1 1 0 011 1v2h2a1 1 0 110 2H4a3 3 0 01-3-3v-2a1 1 0 012 0v2zm15 0a1 1 0 012 0v2a3 3 0 01-3 3h-2a1 1 0 110-2h2v-2a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="isFullscreen" x-cloak viewBox="0 0 20 20" fill="currentColor" width="18" height="18" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7 3a1 1 0 10-2 0v2a2 2 0 01-2 2H1a1 1 0 100 2h2a4 4 0 004-4V3zm6 0a4 4 0 004 4h2a1 1 0 100-2h-2a2 2 0 01-2-2V1a1 1 0 10-2 0v2zM1 13a1 1 0 100 2h2a2 2 0 012 2v2a1 1 0 102 0v-2a4 4 0 00-4-4H1zm18 0h-2a4 4 0 00-4 4v2a1 1 0 102 0v-2a2 2 0 012-2h2a1 1 0 100-2z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                @endif
            </nav>
        @endif
    </div>

    <style>
        .slidewire-shell { width: 100%; height: 100dvh; min-height: 100dvh; overflow: hidden; }
        .slidewire-stage { position: relative; width: 100%; height: 100%; overflow: hidden; }
        .slidewire-frame { position: absolute; inset: 0; display: flex; justify-content: center; overflow-y: auto; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; padding: clamp(1rem, 3vw, 3rem); transform-origin: center center; isolation: isolate; box-sizing: border-box; }
        .slidewire-frame.is-idle { opacity: 0; visibility: hidden; pointer-events: none; }
        .slidewire-frame.is-active,
        .slidewire-frame.is-leaving { visibility: visible; }
        .slidewire-frame::before { content: ""; position: absolute; inset: 0; background: rgb(3 8 20 / var(--slidewire-background-opacity, 0)); z-index: -1; }
        .slidewire-background-media { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; border: 0; z-index: -2; }
        .slidewire-content { width: min(1400px, 94vw); margin-block: auto; }
        .slidewire-controls { position: absolute; left: 50%; bottom: .9rem; transform: translateX(-50%); display: flex; align-items: center; justify-content: center; gap: .5rem; padding: .45rem .7rem; border-radius: 999px; backdrop-filter: blur(8px); background: rgb(2 6 23 / 45%); z-index: 30; }
        .slidewire-controls button { border: 1px solid rgb(148 163 184 / 50%); border-radius: 999px; padding: 0.45rem 0.65rem; background: rgb(15 23 42 / 95%); color: #f8fafc; font-size: .95rem; display: inline-flex; align-items: center; justify-content: center; transition: opacity .15s ease; }
        .slidewire-controls button:disabled { opacity: .35; cursor: not-allowed; }
        .slidewire-controls button svg { display: block; }
        .slidewire-progress { position: absolute; top: 0; left: 0; z-index: 35; height: 6px; width: 100%; background: rgb(15 23 42 / 40%); }
        .slidewire-progress-bar { height: 100%; background: linear-gradient(90deg, #38bdf8, #f8fafc); transition: width .2s ease; }
        .slidewire-fragment { opacity: 0; transform: translateY(10px); transition: opacity .2s ease, transform .2s ease; }
        .slidewire-fragment.slidewire-fragment-visible { opacity: 1; transform: translateY(0); }
        .slidewire-content h1, .slidewire-content h2, .slidewire-content h3,
        .slidewire-content h4, .slidewire-content h5, .slidewire-content h6 { line-height: 1.2; }
        .slidewire-content pre.phiki,
        .slidewire-content pre.slidewire-code { padding: 1.25rem 1.5rem; margin: 1rem 0; border-radius: 0.75rem; overflow-x: auto; line-height: 1.6; }
        .slidewire-content pre.slidewire-code { background: #24292e; color: #e1e4e8; }
        .slidewire-content pre code, .slidewire-content code { font-family: {{ $codeFontFamily }}; }
        .slidewire-content .slidewire-diagram { background: transparent; opacity: 0; color: transparent; }
        .slidewire-content .slidewire-diagram[data-processed] { opacity: 1; transition: opacity .6s ease-out .1s; }
        .slidewire-content .slidewire-diagram svg { max-width: 100%; height: auto; }
        .slidewire-text { display: block; }
        .slidewire-image { display: block; max-width: 100%; height: auto; }
        .slidewire-text-vertical { writing-mode: vertical-rl; text-orientation: mixed; }
        .slidewire-typewriter { display: inline-block; white-space: nowrap; overflow: hidden; }
        .slidewire-typewriter::after { content: ''; display: inline-block; width: 0.08em; height: 1em; margin-left: 0.12em; vertical-align: -0.12em; background: currentColor; animation: slidewire-caret 1s step-end infinite; }

        @@keyframes slidewire-caret {
            0%, 50% { opacity: 1; }
            50.01%, 100% { opacity: 0; }
        }

        @@media (prefers-reduced-motion: reduce) {
            .slidewire-fragment { transition: none; transform: none; }
            .slidewire-content .slidewire-diagram[data-processed] { transition: none; }
            .slidewire-typewriter::after { animation: none; opacity: 0; }
        }
    </style>

    @script
        <script>
            window.slidewireDeck = function ($wire, index, fragment, count, slideThemes, configuredThemes, defaultTheme, slideTransitionDurations, slideAutoSlides, pauseOnInteraction, gridShape, slideCoords, themeTypography) {
            return {
                $wire,
                index,
                fragment,
                count,
                slideThemes,
                configuredThemes,
                defaultTheme,
                slideTransitionDurations,
                slideAutoSlides,
                pauseOnInteraction,
                gridShape,
                slideCoords,
                themeTypography: themeTypography || {},
                touchStartX: null,
                touchStartY: null,
                touchScrollState: null,
                hashUpdateQueued: false,
                isFullscreen: false,
                autoAnimateSnapshot: null,
                leavingIndex: null,
                isTransitioning: false,
                transitionTimeout: null,
                autoSlideTimeout: null,
                reducedMotionQuery: null,
                init() {
                    this.reducedMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
                    this.syncFromHash();
                    this.refreshFragments();
                    this.setupAutoSlide();
                    this.renderDiagrams();
                    this.observeDiagrams();

                    this.$nextTick(() => {
                        this.resetAnimatedNodes(this.activeSlide());
                        this.playElementAnimations(this.activeSlide(), 'in');
                    });

                    window.addEventListener('popstate', () => this.syncFromHash());
                    document.addEventListener('fullscreenchange', () => {
                        this.isFullscreen = document.fullscreenElement !== null;
                    });

                    this.$el.addEventListener('touchstart', (event) => {
                        this.touchStartX = event.touches[0].clientX;
                        this.touchStartY = event.touches[0].clientY;
                        this.touchScrollState = this.captureScrollState(event.target);
                    }, { passive: true });

                    this.$el.addEventListener('touchend', (event) => {
                        if (this.touchStartX === null || this.touchStartY === null) {
                            return;
                        }

                        const dx = event.changedTouches[0].clientX - this.touchStartX;
                        const dy = event.changedTouches[0].clientY - this.touchStartY;

                        if (Math.abs(dx) < 35 && Math.abs(dy) < 35) {
                            return;
                        }

                        if (Math.abs(dx) > Math.abs(dy)) {
                            if (dx < 0) {
                                this.navigateRight();
                            } else {
                                this.navigateLeft();
                            }
                        } else {
                            if (this.shouldPreserveVerticalScroll(dy)) {
                                this.touchStartX = null;
                                this.touchStartY = null;
                                this.touchScrollState = null;

                                return;
                            }

                            if (dy < 0) {
                                this.navigateDown();
                            } else {
                                this.navigateUp();
                            }
                        }

                        this.touchStartX = null;
                        this.touchStartY = null;
                        this.touchScrollState = null;
                    }, { passive: true });

                    this.$watch('index', (value, oldValue) => {
                        this.queueHashUpdate();
                        this.scrollActiveSlideIntoView(value);
                        this.playTransition(oldValue, value);
                        this.refreshFragments();
                        this.playAutoAnimate(oldValue, value);
                        this.setupAutoSlide();
                        this.renderDiagrams();
                        this.$nextTick(() => {
                            this.resetAnimatedNodes(this.activeSlide());
                            this.playElementAnimations(this.activeSlide(), 'in');
                        });
                    });

                    this.$watch('fragment', () => {
                        this.queueHashUpdate();
                        this.refreshFragments();
                        this.setupAutoSlide();
                    });
                },
                currentCoords() {
                    return this.slideCoords[this.index] || { h: 0, v: 0 };
                },
                findFlatIndex(h, v) {
                    for (let i = 0; i < this.slideCoords.length; i++) {
                        if (this.slideCoords[i].h === h && this.slideCoords[i].v === v) {
                            return i;
                        }
                    }

                    return -1;
                },
                canGoLeft() {
                    if (this.fragment > -1) {
                        return true;
                    }

                    const { h } = this.currentCoords();

                    return h > 0;
                },
                canGoRight() {
                    if (this.fragment < this.fragmentCountForSlide() - 1) {
                        return true;
                    }

                    const { h } = this.currentCoords();

                    return h < this.gridShape.length - 1;
                },
                canGoUp() {
                    const { v } = this.currentCoords();

                    return v > 0;
                },
                canGoDown() {
                    const { h, v } = this.currentCoords();
                    const maxV = (this.gridShape[h] || 1) - 1;

                    return v < maxV;
                },
                navigateRight() {
                    this.interruptAutoSlide();
                    const { h } = this.currentCoords();

                    if (h >= this.gridShape.length - 1) {
                        return;
                    }

                    const target = this.findFlatIndex(h + 1, 0);

                    if (target >= 0) {
                        this.captureAutoAnimateSnapshot(target);
                        this.$wire.goToSlide(target);
                    }
                },
                navigateLeft() {
                    this.interruptAutoSlide();
                    const { h } = this.currentCoords();

                    if (h <= 0) {
                        return;
                    }

                    const target = this.findFlatIndex(h - 1, 0);

                    if (target >= 0) {
                        this.captureAutoAnimateSnapshot(target);
                        this.$wire.goToSlide(target);
                    }
                },
                navigateDown() {
                    this.interruptAutoSlide();
                    this.$wire.navigateDown();
                },
                navigateUp() {
                    this.interruptAutoSlide();
                    this.$wire.navigateUp();
                },
                onArrowKey(event, direction) {
                    if (direction === 'up' || direction === 'down') {
                        if (this.canScrollActiveSlide(direction)) {
                            return;
                        }
                    }

                    event.preventDefault();

                    if (direction === 'right') {
                        this.next();

                        return;
                    }

                    if (direction === 'left') {
                        this.previous();

                        return;
                    }

                    if (direction === 'down') {
                        this.navigateDown();

                        return;
                    }

                    this.navigateUp();
                },
                buildHash() {
                    const { h, v } = this.currentCoords();
                    const f = this.fragment;
                    let hash;

                    if (v > 0) {
                        hash = `#/slide/${h + 1}/${v + 1}`;
                    } else {
                        hash = `#/slide/${h + 1}`;
                    }

                    if (f > -1) {
                        hash += `/f/${f}`;
                    }

                    return hash;
                },
                updateHash() {
                    const hash = this.buildHash();

                    if (window.location.hash === hash) {
                        return;
                    }

                    history.pushState(null, '', hash);
                },
                queueHashUpdate() {
                    if (this.hashUpdateQueued) {
                        return;
                    }

                    this.hashUpdateQueued = true;

                    queueMicrotask(() => {
                        this.hashUpdateQueued = false;
                        this.updateHash();
                    });
                },
                frameClass(slideIndex) {
                    if (slideIndex === this.index) {
                        return 'is-active';
                    }

                    if (slideIndex === this.leavingIndex) {
                        return 'is-leaving';
                    }

                    return 'is-idle';
                },
                currentThemeClass() {
                    const activeTheme = this.slideThemes[this.index] || this.defaultTheme || 'default';
                    const classes = this.configuredThemes[activeTheme] || this.configuredThemes[this.defaultTheme] || '';

                    return classes || '';
                },
                parseFragmentFromHash() {
                    const fMatch = window.location.hash.match(/\/f\/(\d+)/);

                    return fMatch ? Number(fMatch[1]) : -1;
                },
                syncFromHash() {
                    const fragment = this.parseFragmentFromHash();
                    const match2d = window.location.hash.match(/#\/slide\/(\d+)\/(\d+)/);

                    if (match2d) {
                        const h = Math.max(0, Number(match2d[1]) - 1);
                        const v = Math.max(0, Number(match2d[2]) - 1);
                        const target = this.findFlatIndex(h, v);

                        if (target >= 0) {
                            this.captureAutoAnimateSnapshot(target);
                            this.$wire.goToSlide(target, fragment);
                        }

                        return;
                    }

                    const match1d = window.location.hash.match(/#\/slide\/(\d+)/);

                    if (match1d) {
                        const h = Math.max(0, Number(match1d[1]) - 1);
                        const target = this.findFlatIndex(h, 0);

                        if (target >= 0) {
                            this.captureAutoAnimateSnapshot(target);
                            this.$wire.goToSlide(target, fragment);
                        }
                    }
                },
                next() {
                    this.interruptAutoSlide();
                    this.captureAutoAnimateSnapshot(this.index + 1);
                    this.$wire.nextSlide();
                },
                previous() {
                    this.interruptAutoSlide();
                    this.captureAutoAnimateSnapshot(this.index - 1);
                    this.$wire.previousSlide();
                },
                transitionDuration(slide, slideIndex) {
                    const base = Number((this.slideTransitionDurations && this.slideTransitionDurations[slideIndex !== undefined ? slideIndex : this.index]) || 350);
                    const speed = (slide?.dataset.transitionSpeed || 'default').toLowerCase();

                    if (speed === 'fast') {
                        return Math.max(140, Math.round(base * 0.55));
                    }

                    if (speed === 'slow') {
                        return Math.round(base * 1.75);
                    }

                    return base;
                },
                prefersReducedMotion() {
                    return this.reducedMotionQuery?.matches === true;
                },
                animatedNodes(slide) {
                    if (!slide) {
                        return [];
                    }

                    return Array.from(slide.querySelectorAll('[data-slidewire-animate]'));
                },
                resetAnimatedNodes(slide) {
                    this.animatedNodes(slide).forEach((node) => {
                        if (node._slidewireAnimation) {
                            node._slidewireAnimation.cancel();
                            node._slidewireAnimation = null;
                        }

                        node.classList.remove('slidewire-typewriter');
                        node.style.opacity = '';
                        node.style.transform = '';
                        node.style.filter = '';
                        node.style.clipPath = '';
                        node.style.textShadow = '';
                    });
                },
                animationDurationMultiplier(speed) {
                    if (speed === 'fast') {
                        return 0.8;
                    }

                    if (speed === 'default') {
                        return 1.7;
                    }

                    return 2.2;
                },
                animationOptions(name, speed = 'default') {
                    const durationMap = {
                        fade: 1100,
                        pop: 420,
                        'zoom-in': 440,
                        'zoom-out': 440,
                        'slide-left': 620,
                        'slide-right': 620,
                        'slide-up': 620,
                        'slide-down': 620,
                        blur: 360,
                        typewriter: 900,
                    };

                    const multiplier = this.animationDurationMultiplier(speed);

                    return {
                        duration: Math.round((durationMap[name] || 280) * multiplier),
                        easing: name === 'typewriter' ? 'steps(18, end)' : 'cubic-bezier(0.22, 0.61, 0.36, 1)',
                        fill: 'both',
                    };
                },
                animationKeyframes(name, phase, node) {
                    const isImage = node.tagName === 'IMG';

                    if (name === 'typewriter' && isImage) {
                        return null;
                    }

                    if (name === 'fade') {
                        return phase === 'in'
                            ? [{ opacity: 0 }, { opacity: 1 }]
                            : [{ opacity: 1 }, { opacity: 0 }];
                    }

                    if (name === 'pop') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'scale(0.82)' }, { opacity: 1, transform: 'scale(1)' }]
                            : [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.92)' }];
                    }

                    if (name === 'zoom-in') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'scale(1.16)' }, { opacity: 1, transform: 'scale(1)' }]
                            : [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(1.08)' }];
                    }

                    if (name === 'zoom-out') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'scale(0.84)' }, { opacity: 1, transform: 'scale(1)' }]
                            : [{ opacity: 1, transform: 'scale(1)' }, { opacity: 0, transform: 'scale(0.84)' }];
                    }

                    if (name === 'slide-left') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'translateX(2rem)' }, { opacity: 1, transform: 'translateX(0)' }]
                            : [{ opacity: 1, transform: 'translateX(0)' }, { opacity: 0, transform: 'translateX(-2rem)' }];
                    }

                    if (name === 'slide-right') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'translateX(-2rem)' }, { opacity: 1, transform: 'translateX(0)' }]
                            : [{ opacity: 1, transform: 'translateX(0)' }, { opacity: 0, transform: 'translateX(2rem)' }];
                    }

                    if (name === 'slide-up') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'translateY(2rem)' }, { opacity: 1, transform: 'translateY(0)' }]
                            : [{ opacity: 1, transform: 'translateY(0)' }, { opacity: 0, transform: 'translateY(-2rem)' }];
                    }

                    if (name === 'slide-down') {
                        return phase === 'in'
                            ? [{ opacity: 0, transform: 'translateY(-2rem)' }, { opacity: 1, transform: 'translateY(0)' }]
                            : [{ opacity: 1, transform: 'translateY(0)' }, { opacity: 0, transform: 'translateY(2rem)' }];
                    }

                    if (name === 'blur') {
                        return phase === 'in'
                            ? [{ opacity: 0, filter: 'blur(18px)' }, { opacity: 1, filter: 'blur(0)' }]
                            : [{ opacity: 1, filter: 'blur(0)' }, { opacity: 0, filter: 'blur(18px)' }];
                    }

                    if (name === 'typewriter') {
                        return phase === 'in'
                            ? [
                                { opacity: 1, clipPath: 'inset(0 100% 0 0)' },
                                { opacity: 1, clipPath: 'inset(0 0 0 0)' },
                            ]
                            : [{ opacity: 1 }, { opacity: 0 }];
                    }

                    return null;
                },
                playElementAnimations(slide, phase) {
                    const nodes = this.animatedNodes(slide);

                    if (nodes.length === 0) {
                        return;
                    }

                    if (this.prefersReducedMotion()) {
                        this.resetAnimatedNodes(slide);

                        return;
                    }

                    nodes.forEach((node, nodeIndex) => {
                        const animationName = phase === 'in' ? (node.dataset.animation || '') : '';
                        const keyframes = this.animationKeyframes(animationName, phase, node);

                        if (!animationName || keyframes === null) {
                            return;
                        }

                        if (animationName === 'typewriter' && phase === 'in') {
                            node.classList.add('slidewire-typewriter');
                        }

                        const animationSpeed = node.dataset.animationSpeed || 'default';
                        const options = this.animationOptions(animationName, animationSpeed);
                        const animation = node.animate(keyframes, {
                            ...options,
                            delay: phase === 'in' ? nodeIndex * 50 : 0,
                        });

                        node._slidewireAnimation = animation;

                        animation.finished
                            .catch(() => {})
                            .finally(() => {
                                if (node._slidewireAnimation === animation) {
                                    node._slidewireAnimation = null;
                                }

                                if (phase === 'out' || animationName !== 'typewriter') {
                                    node.classList.remove('slidewire-typewriter');
                                }

                                if (phase === 'out') {
                                    node.style.opacity = '';
                                    node.style.transform = '';
                                    node.style.filter = '';
                                    node.style.clipPath = '';
                                    node.style.textShadow = '';
                                }
                            });
                    });
                },
                playTransition(oldIndex, newIndex) {
                    if (oldIndex === undefined || oldIndex === null || oldIndex === newIndex) {
                        return;
                    }

                    const fromSlide = this.$refs[`slide${oldIndex}`];
                    const toSlide = this.$refs[`slide${newIndex}`];

                    if (!fromSlide || !toSlide) {
                        this.leavingIndex = null;

                        return;
                    }

                    // Let auto-animate handle the transition to avoid overlap flicker.
                    if (this.shouldAutoAnimate(fromSlide, toSlide)) {
                        this.leavingIndex = null;
                        this.isTransitioning = false;

                        return;
                    }

                    const transition = (toSlide.dataset.transition || 'slide').toLowerCase();
                    const direction = newIndex > oldIndex ? 1 : -1;
                    const duration = this.transitionDuration(toSlide, newIndex);
                    const easing = 'cubic-bezier(0.22, 0.61, 0.36, 1)';

                    const fromCoords = this.slideCoords[oldIndex] || { h: 0, v: 0 };
                    const toCoords = this.slideCoords[newIndex] || { h: 0, v: 0 };
                    const isVertical = fromCoords.h === toCoords.h && fromCoords.v !== toCoords.v;

                    this.leavingIndex = oldIndex;
                    this.isTransitioning = true;

                    if (this.transitionTimeout) {
                        window.clearTimeout(this.transitionTimeout);
                    }

                    if (transition === 'none') {
                        this.transitionTimeout = window.setTimeout(() => {
                            this.leavingIndex = null;
                            this.isTransitioning = false;
                        }, 20);

                        return;
                    }

                    const run = (el, keyframes) => el.animate(keyframes, {
                        duration,
                        easing,
                        fill: 'both',
                    });

                    if (transition === 'fade') {
                        run(fromSlide, [{ opacity: 1 }, { opacity: 0 }]);
                        run(toSlide, [{ opacity: 0 }, { opacity: 1 }]);
                    } else if (transition === 'zoom') {
                        run(fromSlide, [
                            { opacity: 1, transform: 'scale(1)' },
                            { opacity: 0, transform: 'scale(0.88)' },
                        ]);
                        run(toSlide, [
                            { opacity: 0, transform: 'scale(1.12)' },
                            { opacity: 1, transform: 'scale(1)' },
                        ]);
                    } else {
                        if (isVertical) {
                            const vDir = toCoords.v > fromCoords.v ? 1 : -1;
                            run(fromSlide, [
                                { opacity: 1, transform: 'translateY(0)' },
                                { opacity: 0, transform: `translateY(${vDir * -18}%)` },
                            ]);
                            run(toSlide, [
                                { opacity: 0, transform: `translateY(${vDir * 18}%)` },
                                { opacity: 1, transform: 'translateY(0)' },
                            ]);
                        } else {
                            run(fromSlide, [
                                { opacity: 1, transform: 'translateX(0)' },
                                { opacity: 0, transform: `translateX(${direction * -18}%)` },
                            ]);
                            run(toSlide, [
                                { opacity: 0, transform: `translateX(${direction * 18}%)` },
                                { opacity: 1, transform: 'translateX(0)' },
                            ]);
                        }
                    }

                    this.transitionTimeout = window.setTimeout(() => {
                        this.leavingIndex = null;
                        this.isTransitioning = false;
                    }, duration + 25);
                },
                toggleFullscreen() {
                    if (document.fullscreenElement) {
                        document.exitFullscreen();

                        return;
                    }

                    const target = document.documentElement;

                    if (target.requestFullscreen) {
                        target.requestFullscreen();
                    }
                },
                slideAutoSlideDuration() {
                    const activeSlide = this.$refs[`slide${this.index}`];

                    if (!activeSlide) {
                        return 0;
                    }

                    const fromSlide = Number(activeSlide.dataset.autoSlide || 0);

                    if (fromSlide > 0) {
                        return fromSlide;
                    }

                    return Number((this.slideAutoSlides && this.slideAutoSlides[this.index]) || 0);
                },
                activeSlide() {
                    return this.$refs[`slide${this.index}`] || null;
                },
                fragmentCountForSlide(slideIndex = this.index) {
                    const slide = this.$refs[`slide${slideIndex}`];

                    if (!slide) {
                        return 0;
                    }

                    return slide.querySelectorAll('[data-fragment]').length;
                },
                captureScrollState(target) {
                    const container = this.activeSlide();

                    if (!container) {
                        return null;
                    }

                    return {
                        container,
                        scrollTop: container.scrollTop,
                        clientHeight: container.clientHeight,
                        scrollHeight: container.scrollHeight,
                    };
                },
                scrollActiveSlideIntoView(slideIndex) {
                    const slide = this.$refs[`slide${slideIndex}`];

                    if (slide) {
                        slide.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                canScrollContainer(direction, state) {
                    if (!state || !state.container) {
                        return false;
                    }

                    if (state.scrollHeight <= state.clientHeight + 1) {
                        return false;
                    }

                    if (direction === 'up') {
                        return state.scrollTop > 1;
                    }

                    return state.scrollTop + state.clientHeight < state.scrollHeight - 1;
                },
                canScrollActiveSlide(direction) {
                    return this.canScrollContainer(direction, this.captureScrollState());
                },
                shouldPreserveVerticalScroll(dy) {
                    if (!this.touchScrollState) {
                        return false;
                    }

                    if (dy < 0) {
                        return this.canScrollContainer('down', this.touchScrollState);
                    }

                    return this.canScrollContainer('up', this.touchScrollState);
                },
                interruptAutoSlide() {
                    if (!this.pauseOnInteraction) {
                        return;
                    }

                    if (this.autoSlideTimeout) {
                        window.clearTimeout(this.autoSlideTimeout);
                        this.autoSlideTimeout = null;
                    }
                },
                setupAutoSlide() {
                    if (this.autoSlideTimeout) {
                        window.clearTimeout(this.autoSlideTimeout);
                        this.autoSlideTimeout = null;
                    }

                    const duration = this.slideAutoSlideDuration();

                    if (duration < 1) {
                        return;
                    }

                    this.autoSlideTimeout = window.setTimeout(() => this.next(), duration);
                },
                shouldAutoAnimate(fromSlide, toSlide) {
                    const fromEnabled = fromSlide?.dataset.autoAnimate === 'true';
                    const toEnabled = toSlide?.dataset.autoAnimate === 'true';

                    return fromEnabled || toEnabled;
                },
                captureAutoAnimateSnapshot(targetIndex) {
                    const fromSlide = this.$refs[`slide${this.index}`];
                    const toSlide = this.$refs[`slide${targetIndex}`];

                    if (!fromSlide || !toSlide || !this.shouldAutoAnimate(fromSlide, toSlide)) {
                        this.autoAnimateSnapshot = null;

                        return;
                    }

                    const nodes = fromSlide.querySelectorAll('[data-auto-animate-id]');
                    const map = new Map();

                    nodes.forEach((node) => {
                        const id = node.getAttribute('data-auto-animate-id');

                        if (!id) {
                            return;
                        }

                        const rect = node.getBoundingClientRect();
                        map.set(id, {
                            left: rect.left,
                            top: rect.top,
                            width: rect.width,
                            height: rect.height,
                            opacity: getComputedStyle(node).opacity,
                        });
                    });

                    this.autoAnimateSnapshot = {
                        targetIndex,
                        duration: Number(toSlide.dataset.autoAnimateDuration || fromSlide.dataset.autoAnimateDuration || 420),
                        easing: toSlide.dataset.autoAnimateEasing || fromSlide.dataset.autoAnimateEasing || 'ease',
                        map,
                    };
                },
                playAutoAnimate(oldValue, value) {
                    const snapshot = this.autoAnimateSnapshot;

                    if (!snapshot || snapshot.targetIndex !== value) {
                        return;
                    }

                    const toSlide = this.$refs[`slide${value}`];

                    if (!toSlide) {
                        this.autoAnimateSnapshot = null;

                        return;
                    }

                    const targetNodes = toSlide.querySelectorAll('[data-auto-animate-id]');

                    targetNodes.forEach((node) => {
                        node.style.opacity = '0';
                    });

                    // Start before paint so targets do not flash in place.
                    requestAnimationFrame(() => {
                        targetNodes.forEach((node) => {
                            node.style.opacity = '';

                            const id = node.getAttribute('data-auto-animate-id');

                            if (!id) {
                                return;
                            }

                            const source = snapshot.map.get(id);

                            if (!source) {
                                node.animate([
                                    { opacity: 0, transform: 'translateY(12px)' },
                                    { opacity: 1, transform: 'translateY(0)' },
                                ], {
                                    duration: Math.max(220, snapshot.duration * 0.7),
                                    easing: snapshot.easing,
                                    fill: 'both',
                                });

                                return;
                            }

                            const rect = node.getBoundingClientRect();
                            const dx = source.left - rect.left;
                            const dy = source.top - rect.top;
                            const sx = source.width > 0 ? source.width / Math.max(1, rect.width) : 1;
                            const sy = source.height > 0 ? source.height / Math.max(1, rect.height) : 1;

                            node.animate([
                                {
                                    transformOrigin: 'top left',
                                    transform: `translate(${dx}px, ${dy}px) scale(${sx}, ${sy})`,
                                    opacity: source.opacity,
                                },
                                {
                                    transformOrigin: 'top left',
                                    transform: 'translate(0, 0) scale(1, 1)',
                                    opacity: 1,
                                },
                            ], {
                                duration: snapshot.duration,
                                easing: snapshot.easing,
                                fill: 'both',
                            });
                        });

                        this.autoAnimateSnapshot = null;
                    });
                },
                observeDiagrams() {
                    // Re-render Mermaid after Livewire morphs replace rendered SVGs.
                    let diagramTimer = null;

                    const observer = new MutationObserver(() => {
                        const stale = this.$el.querySelectorAll('[data-slidewire-diagram]');
                        let needsRender = false;

                        for (const node of stale) {
                            if (!node.querySelector('svg')) {
                                needsRender = true;
                                break;
                            }
                        }

                        if (!needsRender) {
                            return;
                        }

                        if (diagramTimer) {
                            clearTimeout(diagramTimer);
                        }

                        diagramTimer = setTimeout(() => {
                            diagramTimer = null;
                            this.renderDiagrams();
                        }, 80);
                    });

                    observer.observe(this.$el, {
                        childList: true,
                        subtree: true,
                    });
                },
                renderDiagrams() {
                    const allDiagrams = this.$el.querySelectorAll('[data-slidewire-diagram]');

                    if (allDiagrams.length === 0) {
                        return;
                    }

                    allDiagrams.forEach((node) => {
                        if (node.hasAttribute('data-processed') && !node.querySelector('svg')) {
                            node.removeAttribute('data-processed');
                        }
                    });

                    const pending = Array.from(this.$el.querySelectorAll('[data-slidewire-diagram]:not([data-processed])'));

                    if (pending.length === 0) {
                        return;
                    }

                    pending.forEach((node) => {
                        if (!node.hasAttribute('data-slidewire-diagram-src')) {
                            node.setAttribute('data-slidewire-diagram-src', node.textContent.trim());
                        }
                    });

                    const run = async () => {
                        const batch = Array.from(this.$el.querySelectorAll('[data-slidewire-diagram]:not([data-processed])'));

                        if (batch.length === 0) {
                            return;
                        }

                        batch.forEach((node) => {
                            const src = node.getAttribute('data-slidewire-diagram-src');

                            if (src && !node.querySelector('svg')) {
                                node.textContent = src;
                            }
                        });

                        const groups = {};

                        batch.forEach((node) => {
                            const theme = node.getAttribute('data-mermaid-theme') || 'dark';

                            if (!groups[theme]) {
                                groups[theme] = [];
                            }

                            groups[theme].push(node);
                        });

                        for (const [theme, nodes] of Object.entries(groups)) {
                            window.mermaid.initialize({ startOnLoad: false, theme: theme });

                            try {
                                await window.mermaid.run({ nodes: nodes });
                            } catch (e) {
                            }
                        }
                    };

                    if (typeof window.mermaid !== 'undefined') {
                        run();

                        return;
                    }

                    if (window._slidewireMermaidLoading) {
                        window._slidewireMermaidCallbacks = window._slidewireMermaidCallbacks || [];
                        window._slidewireMermaidCallbacks.push(run);

                        return;
                    }

                    window._slidewireMermaidLoading = true;
                    window._slidewireMermaidCallbacks = [run];

                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js';
                    script.onload = () => {
                        window.mermaid.initialize({ startOnLoad: false, theme: 'dark' });
                        const callbacks = window._slidewireMermaidCallbacks || [];
                        window._slidewireMermaidCallbacks = [];

                        callbacks.forEach((cb) => cb());
                    };
                    document.head.appendChild(script);
                },
                refreshFragments() {
                    this.$nextTick(() => {
                        const slide = this.$refs[`slide${this.index}`];

                        if (!slide) {
                            return;
                        }

                        const nodes = slide.querySelectorAll('[data-fragment]');
                        let lastVisible = null;

                        nodes.forEach((node, currentIndex) => {
                            const explicit = node.getAttribute('data-fragment-index');
                            const fragmentIndex = explicit === null ? currentIndex : Number(explicit);
                            const isVisible = fragmentIndex <= this.fragment;

                            node.classList.toggle('slidewire-fragment-visible', isVisible);

                            if (isVisible) {
                                lastVisible = node;
                            }
                        });

                        if (lastVisible) {
                            lastVisible.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        } else if (nodes.length > 0) {
                            slide.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    });
                }
            }
        }
        </script>
    @endscript
</div>
