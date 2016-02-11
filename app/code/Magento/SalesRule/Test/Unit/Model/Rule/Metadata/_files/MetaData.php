<?php
return [
    'rule_information' =>
        [
            'children' =>
                [
                    'website_ids' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'is_active' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => __('Active'),
                                                                    'value' => '1',
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => __('Inactive'),
                                                                    'value' => '0',
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'customer_group_ids' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'coupon_type' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => 'couponType1',
                                                                    'value' => 'key1',
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => 'couponType2',
                                                                    'value' => 'key2',
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'is_rss' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => __('Yes'),
                                                                    'value' => '1',
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => __('No'),
                                                                    'value' => '0',
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],
    'actions' =>
        [
            'children' =>
                [
                    'simple_action' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => __('Percent of product price discount'),
                                                                    'value' => 'by_percent',
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => __('Fixed amount discount'),
                                                                    'value' => 'by_fixed',
                                                                ],
                                                            2 =>
                                                                [
                                                                    'label' => __('Fixed amount discount for whole cart'),
                                                                    'value' => 'by_percent',
                                                                ],
                                                            3 =>
                                                                [
                                                                    'label' => __('Buy X get Y free (discount amount is Y)'),
                                                                    'value' => 'buy_x_get_y',
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'discount_amount' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'value' => '0',
                                                ],
                                        ],
                                ],
                        ],
                    'discount_qty' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'value' => '0',
                                                ],
                                        ],
                                ],
                        ],
                    'apply_to_shipping' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => __('Yes'),
                                                                    'value' => '1'
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => __('No'),
                                                                    'value' => '0'
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                    'stop_rules_processing' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [

                                                    'options' =>
                                                        [
                                                            0 =>
                                                                [
                                                                    'label' => __('Yes'),
                                                                    'value' => '1'
                                                                ],
                                                            1 =>
                                                                [
                                                                    'label' => __('No'),
                                                                    'value' => '0'
                                                                ],
                                                        ],
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],
    'labels' =>
        [
            'children' =>
                [
                    'store_labels[0]' =>
                        [
                            'arguments' =>
                                [
                                    'data' =>
                                        [
                                            'config' =>
                                                [
                                                    'value' => 'label0',
                                                ],
                                        ],
                                ],
                        ],
                ],
        ],
];
