<?php

return [
    [
        'key'    => 'sales.payment_methods.razorpay',
        'info'   => 'RazorPay extension created for bagisto by wontonee. <span style="color: blue;"><a href="https://myapps.wontonee.com/" target="_blank" style="color: blue;">   <i class="fas fa-external-link-alt"></i>Get License</a></span>
',
        'name'   => 'Razorpay',
        'sort'   => 5,
        'fields' => [
            [
                'name'          => 'title',
                'title'         => 'RazorPay Payment Gateway',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'description',
                'title'         => '',
                'type'          => 'textarea',
                'channel_based' => true,
                'locale_based'  => true,
            ],
            [
                'name'          => 'image',
                'title'         => 'Logo',
                'type'          => 'image',
                'channel_based' => false,
                'locale_based'  => false,
                'validation'    => 'mimes:bmp,jpeg,jpg,png,webp',
            ],
            [
                'name'          => 'license_keyid',
                'title'         => 'License',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => false,
            ],

            [
                'name'          => 'key_id',
                'title'         => 'key id',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'secret',
                'title'         => 'key secret',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ],
            [
                'name'          => 'active',
                'title'         => 'admin::app.configuration.index.sales.payment-methods.status',
                'type'          => 'boolean',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ]
        ]
    ]
];
