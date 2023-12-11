<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Wishlist\Product\Composite\Wishlist;

use Magento\Backend\Model\Session;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\TestFramework\Wishlist\Model\GetWishlistByCustomerId;
use Magento\Wishlist\Model\Item;

/**
 * Tests for update wish list items.
 *
 * @magentoAppArea adminhtml
 */
class UpdateTest extends AbstractBackendController
{
    /** @var GetWishlistByCustomerId */
    private $getWishlistByCustomerId;

    /** @var SerializerInterface */
    private $json;

    /** @var Session */
    private $session;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->getWishlistByCustomerId = $this->_objectManager->get(GetWishlistByCustomerId::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->session = $this->_objectManager->get(Session::class);
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_simple_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateItem(): void
    {
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1');
        $this->assertNotNull($item);
        $params = ['id' => $item->getId(), 'qty' => 5];
        $this->dispatchUpdateItemRequest($params);
        $this->assertEquals($params['qty'], $this->getWishlistByCustomerId->getItemBySku(1, 'simple-1')->getQty());
    }

    /**
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_configurable_product.php
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testUpdateItemOption(): void
    {
        $item = $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product');
        $this->assertNotNull($item);
        $params = [
            'id' => $item->getId(),
            'super_attribute' => $this->performConfigurableOption($item->getProduct()),
            'qty' => 5,
        ];
        $this->dispatchUpdateItemRequest($params);
        $this->assertUpdatedItem(
            $this->getWishlistByCustomerId->getItemBySku(1, 'Configurable product'),
            $params
        );
    }

    /**
     * @return void
     */
    public function testUpdateNotExistingItem(): void
    {
        $this->dispatchUpdateItemRequest(['id' => 989]);
        $this->assertTrue($this->session->getCompositeProductResult()->getError());
        $this->assertEquals(
            (string)__('Please load Wish List item.'),
            $this->session->getCompositeProductResult()->getMessage()
        );
    }

    /**
     * @return void
     */
    public function testUpdateWithoutParams(): void
    {
        $this->dispatchUpdateItemRequest([]);
        $this->assertTrue($this->session->getCompositeProductResult()->getError());
        $this->assertEquals(
            (string)__('Please define Wish List item ID.'),
            $this->session->getCompositeProductResult()->getMessage()
        );
    }
    /**
     * Assert updated item in wish list.
     *
     * @param Item $item
     * @param array $expectedData
     * @return void
     */
    private function assertUpdatedItem(Item $item, array $expectedData): void
    {
        $this->assertEquals($expectedData['qty'], $item->getQty());
        $buyRequestOption = $this->json->unserialize($item->getOptionByCode('info_buyRequest')->getValue());
        foreach ($expectedData as $key => $value) {
            $this->assertEquals($value, $buyRequestOption[$key]);
        }
    }

    /**
     * Perform configurable option to select.
     *
     * @param ProductInterface $product
     * @return array
     */
    private function performConfigurableOption(ProductInterface $product): array
    {
        $configurableOptions = $product->getTypeInstance()->getConfigurableOptions($product);
        $attributeId = key($configurableOptions);
        $option = reset($configurableOptions[$attributeId]);

        return [$attributeId => $option['value_index']];
    }

    /**
     * Dispatch update wish list item request.
     *
     * @param array $params
     * @return void
     */
    private function dispatchUpdateItemRequest(array $params): void
    {
        $this->getRequest()->setParams($params)->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/customer/wishlist_product_composite_wishlist/update');
        $this->assertRedirect($this->stringContains('backend/catalog/product/showUpdateResult/'));
    }
}
