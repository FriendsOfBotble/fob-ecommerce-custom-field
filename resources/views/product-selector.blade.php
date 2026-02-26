<x-plugins-ecommerce::box-search-advanced
    type="product"
    :placeholder="trans('plugins/ecommerce::products.search_products')"
    :shown="$products->isNotEmpty()"
    :heading="trans('plugins/fob-ecommerce-custom-field::custom-field.select_products')"
    template="selected_product_list_template"
>
    <input
        name="product_ids"
        type="hidden"
        value="{{ $customField->id ? implode(',', array_filter((array) $customField->product_ids)) : '' }}"
    />

    <x-slot:items>
        @foreach ($products as $product)
            <div class="list-group-item" data-product-id="{{ $product->id }}">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span
                            class="avatar"
                            style="background-image: url('{{ RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage()) }}')"
                        ></span>
                    </div>
                    <div class="col text-truncate">
                        <a href="{{ route('products.edit', $product->id) }}" class="text-body d-block" target="_blank">
                            {{ $product->name }}
                        </a>
                    </div>
                    <div class="col-auto">
                        <a
                            href="javascript:void(0)"
                            class="text-decoration-none list-group-item-actions"
                            data-bb-toggle="product-delete-item"
                            data-bb-target="{{ $product->id }}"
                            title="{{ trans('plugins/ecommerce::products.delete') }}"
                        >
                            <x-core::icon name="ti ti-x" class="text-secondary" />
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </x-slot:items>
</x-plugins-ecommerce::box-search-advanced>

@push('footer')
<x-core::custom-template id="selected_product_list_template">
    <div class="list-group-item" data-product-id="__id__">
        <div class="row align-items-center">
            <div class="col-auto">
                <span
                    class="avatar"
                    style="background-image: url('__image__')"
                ></span>
            </div>
            <div class="col text-truncate">
                <a href="__url__" class="text-body d-block text-truncate" target="_blank">
                    __name__
                </a>
            </div>
            <div class="col-auto">
                <a
                    href="javascript:void(0)"
                    class="text-decoration-none list-group-item-actions"
                    data-bb-toggle="product-delete-item"
                    data-bb-target="__id__"
                    title="{{ trans('plugins/ecommerce::products.delete') }}"
                >
                    <x-core::icon name="ti ti-x" class="text-secondary" />
                </a>
            </div>
        </div>
    </div>
</x-core::custom-template>

<script>
    $(() => {
        const $productBox = $('#product-selector-box');
        const $displayLocation = $('input[name="display_location"]');
        const $applyTo = $('input[name="apply_to"]');

        function toggleProductSelector() {
            const isProduct = $displayLocation.filter(':checked').val() === @json(\FriendsOfBotble\EcommerceCustomField\Enums\DisplayLocation::PRODUCT);
            const isSpecific = $applyTo.filter(':checked').val() === 'specific';

            $productBox.toggle(isProduct && isSpecific);
        }

        $displayLocation.on('change', toggleProductSelector);
        $applyTo.on('change', toggleProductSelector);

        toggleProductSelector();
    });
</script>
@endpush
