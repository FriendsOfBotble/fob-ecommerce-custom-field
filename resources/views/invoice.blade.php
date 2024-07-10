@foreach($customFieldValues as $customFieldValue)
    <p class="mb-1">
        {{ $customFieldValue->customField->label }}:
        <strong class="text-warning">{{ $customFieldValue->value }}</strong>
    </p>
@endforeach
