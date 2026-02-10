<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Save;

use Magento\Catalog\Controller\Adminhtml\Product\Save\DeleteCustomOptionsTest as SimpleProductOptionsTest;

/**
 * Base test cases for delete configurable product custom option with type "field".
 * Option deleting via product controller action save.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 * @magentoDataFixture Magento/ConfigurableProduct/_files/product_configurable.php
 */
class DeleteCustomOptionsTest extends SimpleProductOptionsTest
{
    /**
     * @var string
     */
    protected $productSku = 'configurable';
}
