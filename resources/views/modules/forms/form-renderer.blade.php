@php
    $layout = $form->setting('layout');
    $style = $form->setting('style');
    $width = in_array($width ?? '', ['contained', 'full'], true) ? $width : $form->setting('width');
    $inline = $layout === 'inline';
@endphp

{{-- Base styles use zero-specificity :where() selectors and the theme's CSS tokens
     (with fallbacks), so any theme can restyle the .cedar-form__* hooks — or
     override this whole view at themes/<theme>/views/modules/forms/. --}}
@once
    <style>
        :where(.cedar-form) { --cf-radius: var(--radius, .5rem); --cf-primary: var(--color-primary, #2563eb); --cf-text: var(--color-secondary, #0f172a); --cf-surface: var(--color-surface, #fff); --cf-line: color-mix(in oklab, var(--cf-text) 20%, var(--cf-surface)); --cf-muted: color-mix(in oklab, var(--cf-text) 65%, var(--cf-surface)); --cf-error: #b91c1c; width: 100%; }
        :where(.cedar-form--contained) { max-width: 36rem; }
        :where(.cedar-form--card) { padding: 1.5rem; border: 1px solid var(--cf-line); border-radius: var(--cf-radius); background: color-mix(in oklab, var(--cf-primary) 6%, var(--cf-surface)); }
        :where(.cedar-form__form) { display: flex; flex-direction: column; gap: 1rem; }
        :where(.cedar-form__field) { display: flex; flex-direction: column; gap: .25rem; min-width: 0; }
        :where(.cedar-form__label) { font-size: .875rem; font-weight: 500; color: var(--cf-text); }
        :where(.cedar-form__label--hidden) { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        :where(.cedar-form__input) { width: 100%; box-sizing: border-box; padding: .5rem .75rem; border: 1px solid var(--cf-line); border-radius: var(--cf-radius); background: var(--cf-surface); color: var(--cf-text); font: inherit; }
        :where(.cedar-form__input:focus) { outline: 2px solid var(--cf-primary); outline-offset: 1px; border-color: var(--cf-primary); }
        :where(.cedar-form__choice) { display: flex; align-items: center; gap: .5rem; font-size: .875rem; color: var(--cf-text); }
        :where(.cedar-form__choices) { display: flex; flex-direction: column; gap: .5rem; }
        :where(.cedar-form__choice input) { accent-color: var(--cf-primary); }
        :where(.cedar-form__error) { margin: 0; font-size: .875rem; color: var(--cf-error); }
        :where(.cedar-form__submit) { align-self: flex-start; padding: .75rem 1.5rem; border: 0; border-radius: var(--cf-radius); background: var(--cf-primary); color: #fff; font: inherit; font-weight: 500; cursor: pointer; }
        :where(.cedar-form__submit:hover) { filter: brightness(1.08); }
        :where(.cedar-form__success) { margin: 0; padding: .75rem 1rem; border-radius: var(--cf-radius); background: color-mix(in oklab, var(--cf-primary) 10%, var(--cf-surface)); color: var(--cf-text); }
        :where(.cedar-form--inline .cedar-form__form) { flex-direction: row; flex-wrap: wrap; align-items: flex-end; }
        :where(.cedar-form--inline .cedar-form__field) { flex: 1 1 12rem; }
        :where(.cedar-form--inline .cedar-form__field--wide, .cedar-form--inline .cedar-form__error--form) { flex-basis: 100%; }
        :where(.cedar-form--inline .cedar-form__submit) { align-self: flex-end; }
    </style>
@endonce

<div class="cedar-form cedar-form--{{ $layout }} cedar-form--{{ $width }} cedar-form--{{ $style }}">
    @if ($submitted)
        <p class="cedar-form__success">{{ $form->success_message ?: "Thanks — we'll be in touch soon." }}</p>
    @else
        <form wire:submit="submit" class="cedar-form__form">
            @error('form') <p class="cedar-form__error cedar-form__error--form">{{ $message }}</p> @enderror

            @foreach ($form->fields as $field)
                @php
                    $wide = in_array($field['type'], ['textarea', 'radio', 'checkbox'], true);
                    $hideLabel = $inline && ! $wide && filled($field['placeholder'] ?? null);
                @endphp
                <div class="cedar-form__field @if ($wide) cedar-form__field--wide @endif">
                    @unless ($field['type'] === 'hidden' || $field['type'] === 'checkbox')
                        <label for="field-{{ $field['name'] }}" class="cedar-form__label @if ($hideLabel) cedar-form__label--hidden @endif">{{ $field['label'] }}</label>
                    @endunless

                    @switch($field['type'])
                        @case('textarea')
                            <textarea id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" rows="4" class="cedar-form__input"></textarea>
                            @break

                        @case('select')
                            <select id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}" class="cedar-form__input">
                                <option value="">{{ $field['placeholder'] ?? 'Select…' }}</option>
                                @foreach ($field['options'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break

                        @case('radio')
                            <div class="cedar-form__choices">
                                @foreach ($field['options'] ?? [] as $option)
                                    <label class="cedar-form__choice">
                                        <input type="radio" wire:model="values.{{ $field['name'] }}" value="{{ $option }}">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case('checkbox')
                            <label class="cedar-form__choice">
                                <input type="checkbox" id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}">
                                {{ $field['label'] }}
                            </label>
                            @break

                        @case('hidden')
                            <input type="hidden" wire:model="values.{{ $field['name'] }}">
                            @break

                        @default
                            <input
                                type="{{ $field['type'] === 'phone' ? 'tel' : $field['type'] }}"
                                id="field-{{ $field['name'] }}"
                                wire:model="values.{{ $field['name'] }}"
                                placeholder="{{ $field['placeholder'] ?? '' }}"
                                class="cedar-form__input"
                            >
                    @endswitch

                    @error('values.'.$field['name']) <p class="cedar-form__error">{{ $message }}</p> @enderror
                </div>
            @endforeach

            {{-- Honeypot: hidden from real visitors via CSS, never via `type="hidden"` (bots skip those). --}}
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($form->captcha_enabled)
                <x-captcha field="captcha_token" />
            @endif

            <button type="submit" class="cedar-form__submit">{{ $form->setting('button_label') }}</button>
        </form>
    @endif
</div>
