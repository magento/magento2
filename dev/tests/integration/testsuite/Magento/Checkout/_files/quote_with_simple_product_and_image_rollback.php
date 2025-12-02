<?php
/**
 * Rollback for quote_with_simple_product_and_image.php fixture.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/product_image_rollback.php');
