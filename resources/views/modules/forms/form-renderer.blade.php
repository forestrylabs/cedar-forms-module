<div>
    @if ($submitted)
        <p class="rounded-[var(--radius)] bg-slate-50 px-4 py-3 text-slate-700">{{ $form->success_message ?: "Thanks — we'll be in touch soon." }}</p>
    @else
        @php
            $inputClasses = 'w-full rounded-[var(--radius)] border border-slate-300 px-3 py-2 text-slate-900 focus:border-[color:var(--color-primary)] focus:outline-none focus:ring-1 focus:ring-[color:var(--color-primary)]';
        @endphp
        <form wire:submit="submit" class="flex max-w-xl flex-col gap-4">
            @error('form') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            @foreach ($form->fields as $field)
                <div class="flex flex-col gap-1">
                    @unless ($field['type'] === 'hidden' || $field['type'] === 'checkbox')
                        <label for="field-{{ $field['name'] }}" class="text-sm font-medium text-slate-700">{{ $field['label'] }}</label>
                    @endunless

                    @switch($field['type'])
                        @case('textarea')
                            <textarea id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}" rows="4" class="{{ $inputClasses }}"></textarea>
                            @break

                        @case('select')
                            <select id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}" class="{{ $inputClasses }}">
                                <option value="">{{ $field['placeholder'] ?? 'Select…' }}</option>
                                @foreach ($field['options'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @break

                        @case('radio')
                            <div class="flex flex-col gap-2">
                                @foreach ($field['options'] ?? [] as $option)
                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                        <input type="radio" wire:model="values.{{ $field['name'] }}" value="{{ $option }}" class="text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]">
                                        {{ $option }}
                                    </label>
                                @endforeach
                            </div>
                            @break

                        @case('checkbox')
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" id="field-{{ $field['name'] }}" wire:model="values.{{ $field['name'] }}" class="rounded text-[color:var(--color-primary)] focus:ring-[color:var(--color-primary)]">
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
                                class="{{ $inputClasses }}"
                            >
                    @endswitch

                    @error('values.'.$field['name']) <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach

            {{-- Honeypot: hidden from real visitors via CSS, never via `type="hidden"` (bots skip those). --}}
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <input type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @if ($form->captcha_enabled)
                <x-captcha field="captcha_token" />
            @endif

            <button type="submit" class="inline-block w-fit rounded-[var(--radius)] bg-[color:var(--color-primary)] px-6 py-3 font-medium text-white">Submit</button>
        </form>
    @endif
</div>
