<?php
/**
 * Rollback for quote_with_shipping_method.php fixture.
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Checkout/_files/quote_with_address_saved_rollback.php');
