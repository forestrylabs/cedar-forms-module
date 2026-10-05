@php
    $form = \Modules\Forms\Models\Form::find($data['form_id'] ?? null);
@endphp
@if ($form)
    <livewire:form-renderer :form="$form" :width="$data['width'] ?? ''" :key="'form-'.$form->id.'-'.($data['width'] ?? '')" />
@endif
