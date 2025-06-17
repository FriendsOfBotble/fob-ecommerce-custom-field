<?php

return [
    [
        'name' => 'Custom Fields for eCommerce',
        'flag' => 'ecommerce-custom-fields.index',
        'parent_flag' => 'plugins.ecommerce',
    ],
    [
        'name' => 'Create',
        'flag' => 'ecommerce-custom-fields.create',
        'parent_flag' => 'ecommerce-custom-fields.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'ecommerce-custom-fields.edit',
        'parent_flag' => 'ecommerce-custom-fields.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'ecommerce-custom-fields.destroy',
        'parent_flag' => 'ecommerce-custom-fields.index',
    ],
];
