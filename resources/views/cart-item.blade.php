<dl class="mt-1">
    @foreach($customFields as $name => $value)
        <div @class(['small d-flex gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $name }}:</dt>
            <dd class="mb-0">
                @if(filter_var($value, FILTER_VALIDATE_URL) && (str_contains($value, '/storage/') || str_contains($value, '/uploads/')))
                    @php
                        $isImage = in_array(strtolower(pathinfo($value, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                    @endphp

                    @if($isImage)
                        <a href="{{ $value }}" target="_blank">
                            <img src="{{ $value }}" alt="{{ $name }}" class="rounded border" style="max-width: 40px; max-height: 40px; object-fit: cover;">
                        </a>
                    @else
                        <a href="{{ $value }}" target="_blank" download class="d-inline-flex align-items-center gap-1 text-decoration-none">
                            <x-core::icon name="ti ti-file" /> {{ basename($value) }}
                        </a>
                    @endif
                @else
                    {{ $value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
