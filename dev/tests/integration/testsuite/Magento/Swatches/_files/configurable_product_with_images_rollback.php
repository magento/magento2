<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Swatches/_files/configurable_product_visual_swatch_attribute_rollback.php'
);
Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/product_image_rollback.php'
);
