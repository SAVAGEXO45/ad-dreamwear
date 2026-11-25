@props(['name', 'label', 'value' => null, 'type' => 'text', 'required' => false, 'hint' => null])
<div class="field">
    <label for="{{ $name }}">{{ $label }}</label>
    @if($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" rows="4" @required($required) @if($hint) aria-describedby="{{ $name }}-hint" @endif @error($name) aria-invalid="true" @enderror {{ $attributes }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" @if(!in_array($type, ['file', 'password'], true)) value="{{ old($name, $value) }}" @endif @required($required) @if($hint) aria-describedby="{{ $name }}-hint" @endif @error($name) aria-invalid="true" @enderror {{ $attributes }}>
    @endif
    @if($hint)<p class="field-hint" id="{{ $name }}-hint">{{ $hint }}</p>@endif
</div>
