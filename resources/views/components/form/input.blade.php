<div class="mb-4 form-group">
    @if($label)
        <label for="{{ $id }}" class="form-usuario-label">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}" value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}" @if($required) required @endif
        class="form-usuario-input @error($name) input-error @enderror"
        autocomplete="off">

    @error($name)
        <p class="input-error-message">{{ $message }}</p>
    @enderror
</div>
