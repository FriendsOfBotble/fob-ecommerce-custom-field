<div class="ec-custom-field-wrapper my-3">
    @foreach($customFields as $key => $customField)
        <div class="mb-3 ec-custom-field">
            <label for="custom_fields-{{ $customField->getKey() }}" class="form-label ec-custom-field-label">{{ $customField->label }}</label>

            @if (in_array($customField->type, ['text', 'number', 'date', 'time', 'datetime']))
                <input
                    type="{{ $customField->type == 'datetime' ? 'datetime-local' : $customField->type }}"
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    id="custom_fields-{{ $customField->getKey() }}"
                    class="form-control ec-custom-field-input"
                    @if ($customField->placeholder)
                        placeholder="{{ $customField->placeholder }}"
                    @endif
                >
            @elseif ($customField->type == 'textarea')
                <textarea
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    id="custom_fields-{{ $customField->getKey() }}"
                    class="form-control ec-custom-field-input"
                    rows="3"
                    @if($customField->placeholder)
                        placeholder="{{ $customField->placeholder }}"
                    @endif
                ></textarea>
            @elseif ($customField->type == 'select')
                <select
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    id="custom_fields-{{ $customField->getKey() }}"
                    class="form-select ec-custom-field-input"
                >
                    @if($customField->placeholder)
                        <option value="">{{ $customField->placeholder }}</option>
                    @endif
                    @foreach($customField->formatted_options as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    @endforeach
</div>
