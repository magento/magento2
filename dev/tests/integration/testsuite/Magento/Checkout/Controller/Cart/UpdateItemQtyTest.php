<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Checkout\Controller\Cart;

use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Serialize\Serializer\Json;

class UpdateItemQtyTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->json = $this->_objectManager->create(Json::class);
        $this->formKey = $this->_objectManager->get(FormKey::class);
        $this->session = $this->_objectManager->create(Session::class);
        $this->productRepository = $this->_objectManager->create(ProductRepositoryInterface::class);
    }

    /**
     * Tests of cart validation.
     *
     * @param array $requestQuantity
     * @param array $expectedResponse
     *
     * @magentoDbIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture Magento/Checkout/_files/quote_with_simple_product.php
     * @dataProvider requestDataProvider
     */
    public function testExecute($requestQuantity, $expectedResponse)
    {
        try {
            /** @var $product Product */
            $product = $this->productRepository->get('simple');
        } catch (\Exception $e) {
            $this->fail('No such product entity');
        }

        $quoteItem = $this->session
            ->getQuote()
            ->getItemByProduct($product);

        $this->assertNotNull($quoteItem, 'Cannot get quote item for simple product');

        $request = [];
        if (!empty($requestQuantity) && is_array($requestQuantity)) {
            $request= [
                'form_key' => $this->formKey->getFormKey(),
                'cart' => [
                    $quoteItem->getId() => $requestQuantity,
                ]
            ];
        }

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue($request);
        $this->dispatch('checkout/cart/updateItemQty');
        $response = $this->getResponse()->getBody();

        $this->assertEquals($this->getErrorMessage($response), $this->getErrorMessage($expectedResponse));
    }

    /**
     * @param $response
     * @return string
     */
    protected function getErrorMessage($response)
    {
        $error = '';
        try {
            $data = is_array($response) ? $response : $this->json->unserialize($response);
            $error = $this->json->unserialize($data['error_message'])[0]['error'];
        } catch (\Exception $e) {
            if (!empty($data['error_message'])) {
                $error = $data['error_message'];
            }
        }
        return $error;
    }

    /**
     * Variations of request data.
     * @returns array
     */
    public static function requestDataProvider(): array
    {
        return [
            [
                'requestQuantity' => [],
                'expectedResponse' => [
                    'success' => false,
                    'error_message' => 'Something went wrong while saving the page.'.
                        ' Please refresh the page and try again.'
                ]
            ],
            [
                'requestQuantity' => ['qty' => 2],
                'expectedResponse' => [
                    'success' => true,
                ]
            ],
            [
                'requestQuantity' => ['qty' => 230],
                'expectedResponse' => [
                    'success' => false,
                    'error_message' => '[{"error":"Not enough items for sale","itemId":3}]']
            ],
        ];
    }
}
