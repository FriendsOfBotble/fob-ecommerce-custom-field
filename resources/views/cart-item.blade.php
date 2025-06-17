<dl class="mt-1">
    @foreach($customFields as $name => $value)
        <div @class(['small d-flex gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $name }}:</dt>
            <dd class="mb-0">
                @if(filter_var($value, FILTER_VALIDATE_URL) && (str_contains($value, '/storage/') || str_contains($value, '/uploads/')))
                    @if(in_array(pathinfo($value, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif', 'webp']))
                        <img src="{{ $value }}" alt="{{ $name }}" style="max-width: 100px; max-height: 100px;" class="img-thumbnail">
                    @else
                        <a href="{{ $value }}" target="_blank" class="text-decoration-none">
                            <i class="ti ti-paperclip"></i> {{ __('View File') }}
                        </a>
                    @endif
                @else
                    {{ $value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
