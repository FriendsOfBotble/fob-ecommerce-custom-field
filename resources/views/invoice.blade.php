@foreach($customFieldValues as $customFieldValue)
    <p class="mb-1">
        {{ $customFieldValue->customField->label }}:
        <strong class="text-warning">
            @if(filter_var($customFieldValue->value, FILTER_VALIDATE_URL) && (str_contains($customFieldValue->value, '/storage/') || str_contains($customFieldValue->value, '/uploads/')))
                @if(in_array(pathinfo($customFieldValue->value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                    <img src="{{ $customFieldValue->value }}" alt="{{ $customFieldValue->customField->label }}" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                @else
                    <a href="{{ $customFieldValue->value }}" target="_blank" class="text-decoration-none">
                        <i class="ti ti-paperclip"></i> {{ __('View File') }}
                    </a>
                @endif
            @else
                {{ $customFieldValue->value }}
            @endif
        </strong>
    </p>
@endforeach
