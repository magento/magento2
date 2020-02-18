<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Section;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Load customer data test class.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea frontend
 */
class LoadTest extends AbstractController
{
    /** @var Session */
    private $customerSession;

    /** @var SerializerInterface */
    private $json;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->customerSession->setCustomerId(null);

        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testLoadInvalidSection(): void
    {
        $expected = [
            'message' => 'The &quot;section&lt;invalid&quot; section source isn&#039;t supported.',
        ];
        $this->dispatch(
            '/customer/section/load/?sections=section<invalid&force_new_section_timestamp=false&_=147066166394'
        );
        $this->assertEquals($this->json->serialize($expected), $this->getResponse()->getBody());
    }

    /**
     * @magentoConfigFixture current_store wishlist/wishlist_link/use_qty 1
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_three.php
     *
     * @return void
     */
    public function testWishListCounterUseQty(): void
    {
        $this->customerSession->setCustomerId(1);
        $response = $this->performWishListSectionRequest();
        $this->assertEquals('3 items', $response['wishlist']['counter']);
    }

    /**
     * @magentoConfigFixture current_store wishlist/wishlist_link/use_qty 0
     * @magentoDataFixture Magento/Wishlist/_files/wishlist_with_product_qty_three.php
     *
     * @return void
     */
    public function testWishListCounterNotUseQty(): void
    {
        $this->customerSession->setCustomerId(1);
        $response = $this->performWishListSectionRequest();
        $this->assertEquals('1 item', $response['wishlist']['counter']);
    }

    /**
     * Perform wish list section request.
     *
     * @return array
     */
    private function performWishListSectionRequest(): array
    {
        $this->getRequest()->setParam('sections', 'wishlist')->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch('customer/section/load');

        return $this->json->unserialize($this->getResponse()->getBody());
    }
}
