<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

return [
    "media" => [
        "Magento_Catalog" => [
            "images" => [
                "swatch_thumb_base" => [
                    "type" => "swatch_thumb",
                    "width" => 75,
                    "height" => 75,
                    "constrain" => false,
                    "aspect_ratio" => false,
                    "frame" => false,
                    "transparency" => false,
                    "background" => [255, 25, 2],
                ],
                "swatch_thumb_medium" => [
                    "type" => "swatch_medium",
                    "width" => 750,
                    "height" => 750,
                    "constrain" => true,
                    "aspect_ratio" => true,
                    "frame" => true,
                    "transparency" => true,
                    "background" => [255, 25, 2],
                ],
                "swatch_thumb_large" => [
                    "type" => "swatch_large",
                    "width" => 1080,
                    "height" => 720,
                    "constrain" => false,
                    "aspect_ratio" => false,
                    "frame" => false,
                    "transparency" => false,
                    "background" => [255, 25, 2],
                ],
                "swatch_thumb_small" => [
                    "type" => "swatch_small",
                    "width" => 100,
                    "height" => 100,
                    "constrain" => true,
                    "aspect_ratio" => true,
                    "frame" => true,
                    "transparency" => true,
                    "background" => [255, 25, 2],
                ]
            ]
        ]
    ]
];
