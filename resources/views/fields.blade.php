<div class="ec-custom-field-wrapper my-3">
    @php
        $hasFileFields = $customFields->whereIn('type', ['file', 'image'])->isNotEmpty();
    @endphp

    @if($hasFileFields)
        <style>
            .ec-file-preview {
                display: none;
                margin-top: 0.75rem;
                padding: 0.75rem;
                background: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                position: relative;
            }
            .ec-file-preview.active { display: flex; align-items: center; gap: 0.75rem; }
            .ec-file-preview-thumbnail {
                flex-shrink: 0;
                width: 64px;
                height: 64px;
                border-radius: 0.375rem;
                overflow: hidden;
                background: #fff;
                border: 1px solid #dee2e6;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .ec-file-preview-thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .ec-file-preview-thumbnail .ec-file-preview-icon {
                color: #6c757d;
                width: 28px;
                height: 28px;
            }
            .ec-file-preview-info { flex: 1; min-width: 0; }
            .ec-file-preview-name {
                font-weight: 500;
                font-size: 0.875rem;
                color: #212529;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .ec-file-preview-size { font-size: 0.8125rem; color: #6c757d; }
            .ec-file-preview-remove {
                flex-shrink: 0;
                width: 28px;
                height: 28px;
                border: none;
                background: #e9ecef;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #6c757d;
                transition: all 0.2s;
            }
            .ec-file-preview-remove:hover { background: #dc3545; color: #fff; }
        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var wrapper = document.querySelector('.ec-custom-field-wrapper');
                if (wrapper) {
                    var form = wrapper.closest('form');
                    if (form && !form.getAttribute('enctype')) {
                        form.setAttribute('enctype', 'multipart/form-data');
                    }
                }

                document.querySelectorAll('.ec-custom-field .bb-file-input').forEach(function(input) {
                    input.addEventListener('change', function() {
                        var preview = input.closest('.ec-custom-field').querySelector('.ec-file-preview');
                        if (!preview) return;

                        if (!input.files || !input.files.length) {
                            preview.classList.remove('active');
                            return;
                        }

                        var file = input.files[0];
                        var thumb = preview.querySelector('.ec-file-preview-thumbnail');
                        var nameEl = preview.querySelector('.ec-file-preview-name');
                        var sizeEl = preview.querySelector('.ec-file-preview-size');

                        nameEl.textContent = file.name;
                        sizeEl.textContent = formatFileSize(file.size);

                        if (file.type.startsWith('image/')) {
                            var reader = new FileReader();
                            reader.onload = function(e) {
                                thumb.innerHTML = '<img src="' + e.target.result + '" alt="' + file.name + '">';
                            };
                            reader.readAsDataURL(file);
                        } else {
                            thumb.innerHTML = '<svg class="ec-file-preview-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/></svg>';
                        }

                        preview.classList.add('active');
                    });
                });

                document.querySelectorAll('.ec-file-preview-remove').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var field = btn.closest('.ec-custom-field');
                        var input = field.querySelector('.bb-file-input');
                        var label = field.querySelector('.bb-file-label');
                        var preview = field.querySelector('.ec-file-preview');

                        input.value = '';
                        label.classList.remove('has-file');
                        label.querySelector('.bb-file-name').textContent = '';
                        preview.classList.remove('active');
                    });
                });

                function formatFileSize(bytes) {
                    if (bytes < 1024) return bytes + ' B';
                    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
                    return (bytes / 1048576).toFixed(1) + ' MB';
                }
            });
        </script>
    @endif

    @foreach($customFields as $key => $customField)
        <div class="mb-3 ec-custom-field">
            <label for="custom_fields-{{ $customField->getKey() }}" class="form-label ec-custom-field-label">{{ $customField->label }}</label>

            @if ($customField->type == 'readonly_text')
                <input
                    type="hidden"
                    name="extras[custom_fields][{{ $customField->getKey() }}]"
                    value="{{ $customField->getDefaultValue() }}"
                >
                <p class="form-control-plaintext ec-custom-field-readonly">{{ $customField->getDefaultValue() }}</p>
            @elseif (in_array($customField->type, ['text', 'number', 'date', 'time', 'datetime']))
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
            @elseif (in_array($customField->type, ['file', 'image']))
                @php
                    $fieldId = 'custom_fields-' . $customField->getKey();
                    $acceptAttr = $customField->getFileAcceptAttribute();
                    if (! $acceptAttr && $customField->type == 'image') {
                        $acceptAttr = 'image/*';
                    }
                    $acceptedTypes = $customField->getFileAcceptedTypes();
                    $maxFileSize = $customField->getMaxFileSize();
                @endphp
                <div class="bb-file-upload-wrapper">
                    <input
                        type="file"
                        name="extras[custom_fields][{{ $customField->getKey() }}]"
                        id="{{ $fieldId }}"
                        class="bb-file-input"
                        @if ($acceptAttr)
                            accept="{{ $acceptAttr }}"
                        @endif
                    >
                    <label for="{{ $fieldId }}" class="bb-file-label">
                        <div class="bb-file-icon">
                            <x-core::icon name="ti ti-cloud-upload" />
                        </div>
                        <div class="bb-file-text">
                            <span class="bb-file-placeholder">{{ $customField->placeholder ?: __('Choose file or drag & drop here') }}</span>
                            <span class="bb-file-name"></span>
                        </div>
                        <span class="bb-file-button">{{ __('Browse') }}</span>
                    </label>
                </div>
                <div class="ec-file-preview">
                    <div class="ec-file-preview-thumbnail"></div>
                    <div class="ec-file-preview-info">
                        <div class="ec-file-preview-name"></div>
                        <div class="ec-file-preview-size"></div>
                    </div>
                    <button type="button" class="ec-file-preview-remove" title="{{ __('Remove') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6l-12 12"/><path d="M6 6l12 12"/></svg>
                    </button>
                </div>
                @if ($acceptedTypes || $maxFileSize)
                    <small class="form-text text-muted d-block mt-2">
                        @if ($customField->type == 'image')
                            {{ __('You can upload the following file types: :types', ['types' => $acceptedTypes ?: 'jpg, jpeg, png, gif, webp, bmp']) }}
                        @else
                            {{ __('You can upload the following file types: :types', ['types' => $acceptedTypes]) }}
                        @endif
                        @if ($maxFileSize)
                            {{ __('and max file size is :sizeMB.', ['size' => $maxFileSize]) }}
                        @endif
                    </small>
                @endif
            @endif
        </div>
    @endforeach
</div>
