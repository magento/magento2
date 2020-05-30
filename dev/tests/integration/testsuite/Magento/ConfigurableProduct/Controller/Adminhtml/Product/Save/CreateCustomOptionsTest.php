<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Controller\Adminhtml\Product\Save\CreateCustomOptionsTest as SimpleProductOptionsTest;

/**
 * Base test cases for configurable product custom options with type "field".
 * Option add via dispatch product controller action save with options data in POST data.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class CreateCustomOptionsTest extends SimpleProductOptionsTest
{
    /**
     * @var string
     */
    protected $productSku = 'configurable';
}
