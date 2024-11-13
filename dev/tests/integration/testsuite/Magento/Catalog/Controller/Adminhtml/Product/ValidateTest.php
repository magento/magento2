<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\UrlRewrite\Model\Exception\UrlAlreadyExistsException;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Catalog\Model\Product\Type;

/**
 * @magentoAppArea adminhtml
 */
class ValidateTest extends AbstractBackendController
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testNotUniqueUrlKey()
    {
        $this->getRequest()
            ->setMethod(HttpRequest::METHOD_POST);

        $postData = [
            'product' => [
                'attribute_set_id' => '4',
                'status' => '1',
                'name' => 'Simple product',
                'sku' => 'simple',
                'type_id' => Type::TYPE_SIMPLE,
                'quantity_and_stock_status' => [
                    'qty' => '10',
                    'is_in_stock' => '1',
                ],
                'website_ids' => [
                    1 => '1',
                ],
                'price' => '100',
            ],
        ];

        $this->getRequest()
            ->setPostValue($postData);
        $this->dispatch('backend/catalog/product/validate/');
        $responseBody = $this->getResponse()
            ->getBody();

        $message = __('The value specified in the URL Key field would generate a URL that already exists.');
        $additionalInfo = __('To resolve this conflict, you can either change the value of the URL Key field '
            . '(located in the Search Engine Optimization section) to a unique value, or change the Request Path fields'
            . ' in all locations listed below:');

        $this->assertStringContainsString((string)$message, $responseBody);
        $this->assertStringContainsString((string)$additionalInfo, $responseBody);
    }
}
