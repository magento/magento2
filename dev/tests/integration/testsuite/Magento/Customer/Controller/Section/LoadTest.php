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
use Magento\Framework\Escaper;

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

    /** @var Escaper */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->escaper = $this->_objectManager->get(Escaper::class);
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
        $message = $this->escaper->escapeHtml('The "section<invalid" section source isn\'t supported.');
        $expected = ['message' => $message];
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
