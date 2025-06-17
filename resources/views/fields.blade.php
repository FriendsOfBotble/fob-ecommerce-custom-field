<div class="ec-custom-field-wrapper my-3">
    @php
        $hasFileFields = $customFields->whereIn('type', ['file', 'image'])->isNotEmpty();
    @endphp

    @if($hasFileFields)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Find the parent form and add enctype for file uploads
                const customFieldWrapper = document.querySelector('.ec-custom-field-wrapper');
                if (customFieldWrapper) {
                    const form = customFieldWrapper.closest('form');
                    if (form && !form.getAttribute('enctype')) {
                        form.setAttribute('enctype', 'multipart/form-data');
                    }
                }

                // Add file validation
                const fileInputs = document.querySelectorAll('.ec-custom-field-input[type="file"]');
                fileInputs.forEach(function(input) {
                    input.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;

                        const fieldId = input.id.replace('custom_fields-', '');
                        const acceptAttr = input.getAttribute('accept');
                        const maxSizeText = input.parentElement.querySelector('.form-text:contains("Maximum file size")');

                        // Validate file type
                        if (acceptAttr && acceptAttr !== 'image/*') {
                            const allowedExtensions = acceptAttr.split(',').map(ext => ext.trim().replace('.', ''));
                            const fileExtension = file.name.split('.').pop().toLowerCase();

                            if (!allowedExtensions.includes(fileExtension)) {
                                alert('Invalid file type. Allowed types: ' + allowedExtensions.join(', '));
                                input.value = '';
                                return;
                            }
                        }

                        // Validate file size
                        if (maxSizeText) {
                            const maxSizeMatch = maxSizeText.textContent.match(/(\d+)\s*MB/);
                            if (maxSizeMatch) {
                                const maxSizeMB = parseInt(maxSizeMatch[1]);
                                const fileSizeMB = file.size / (1024 * 1024);

                                if (fileSizeMB > maxSizeMB) {
                                    alert('File size exceeds maximum allowed size of ' + maxSizeMB + ' MB');
                                    input.value = '';
                                    return;
                                }
                            }
                        }
                    });
                });
            });
        </script>
    @endif

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
            @elseif ($customField->type == 'file')
                <input
                    type="file"
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    id="custom_fields-{{ $customField->getKey() }}"
                    class="form-control ec-custom-field-input"
                    @if ($customField->getFileAcceptAttribute())
                        accept="{{ $customField->getFileAcceptAttribute() }}"
                    @endif
                    @if ($customField->placeholder)
                        data-placeholder="{{ $customField->placeholder }}"
                    @endif
                >
                @if ($customField->getFileAcceptedTypes())
                    <small class="form-text text-muted">
                        {{ __('Allowed file types: :types', ['types' => $customField->getFileAcceptedTypes()]) }}
                    </small>
                @endif
                @if ($customField->getMaxFileSize())
                    <small class="form-text text-muted">
                        {{ __('Maximum file size: :size MB', ['size' => $customField->getMaxFileSize()]) }}
                    </small>
                @endif
            @elseif ($customField->type == 'image')
                <input
                    type="file"
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    id="custom_fields-{{ $customField->getKey() }}"
                    class="form-control ec-custom-field-input"
                    @if ($customField->getFileAcceptAttribute())
                        accept="{{ $customField->getFileAcceptAttribute() }}"
                    @else
                        accept="image/*"
                    @endif
                    @if ($customField->placeholder)
                        data-placeholder="{{ $customField->placeholder }}"
                    @endif
                >
                @if ($customField->getFileAcceptedTypes())
                    <small class="form-text text-muted">
                        {{ __('Allowed image types: :types', ['types' => $customField->getFileAcceptedTypes()]) }}
                    </small>
                @endif
                @if ($customField->getMaxFileSize())
                    <small class="form-text text-muted">
                        {{ __('Maximum file size: :size MB', ['size' => $customField->getMaxFileSize()]) }}
                    </small>
                @endif
            @endif
        </div>
    @endforeach
</div>
