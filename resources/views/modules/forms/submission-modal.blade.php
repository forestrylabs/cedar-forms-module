<dl class="space-y-3">
    @foreach ($record->form->fields as $field)
        <div>
            <dt class="font-medium">{{ $field['label'] }}</dt>
            <dd>
                @php $value = $record->data[$field['name']] ?? null; @endphp
                {{ $field['type'] === 'checkbox' ? ($value ? 'Yes' : 'No') : (blank($value) ? '—' : $value) }}
            </dd>
        </div>
    @endforeach
    <div>
        <dt class="font-medium">Submitted</dt>
        <dd>{{ $record->created_at->format('Y-m-d H:i') }} from {{ $record->ip_address }}</dd>
    </div>
</dl>
