<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Catalog\Controller\Product;

use Magento\Framework\Message\MessageInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CompareTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $formKey;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->formKey = $this->_objectManager->get(\Magento\Framework\Data\Form\FormKey::class);
        $this->productRepository = $this->_objectManager->create(\Magento\Catalog\Model\ProductRepository::class);
    }

    /**
     * Test adding product to compare list.
     *
     * @return void
     */
    public function testAddAction()
    {
        $this->_requireVisitorWithNoProducts();
        $product = $this->productRepository->get('simple_product_1');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch(
            sprintf(
                'catalog/product_compare/add/product/%s/form_key/%s?nocookie=1',
                $product->getEntityId(),
                $this->formKey->getFormKey()
            )
        );

        $this->assertSessionMessages(
            $this->equalTo(['You added product Simple Product 1 Name to the comparison list.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([$product->getEntityId()]);
    }

    /**
     * Test adding disabled product to compare list.
     *
     * @return void
     */
    public function testAddActionForDisabledProduct()
    {
        $this->_requireVisitorWithNoProducts();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->setProductDisabled('simple_product_1');

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch(
            sprintf(
                'catalog/product_compare/add/product/%s/form_key/%s?nocookie=1',
                $product->getEntityId(),
                $this->formKey->getFormKey()
            )
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([]);
    }

    /**
     * Test comparing a product.
     *
     * @return void
     */
    public function testIndexActionAddProducts()
    {
        $this->_requireVisitorWithNoProducts();
        $product = $this->productRepository->get('simple_product_2');
        $this->dispatch('catalog/product_compare/index/items/' . $product->getEntityId());

        $this->assertRedirect($this->equalTo('http://localhost/index.php/catalog/product_compare/index/'));

        $this->_assertCompareListEquals([$product->getEntityId()]);
    }

    /**
     * Test removing a product from compare list.
     *
     * @return void
     */
    public function testRemoveAction()
    {
        $this->_requireVisitorWithTwoProducts();
        $product = $this->productRepository->get('simple_product_2');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('catalog/product_compare/remove/product/' . $product->getEntityId());

        $this->assertSessionMessages(
            $this->equalTo(['You removed product Simple Product 2 Name from the comparison list.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect();
        $restProduct = $this->productRepository->get('simple_product_1');
        $this->_assertCompareListEquals([$restProduct->getEntityId()]);
    }

    /**
     * Test removing a disabled product from compare list.
     *
     * @return void
     */
    public function testRemoveActionForDisabledProduct()
    {
        $this->_requireVisitorWithTwoProducts();
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->setProductDisabled('simple_product_1');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('catalog/product_compare/remove/product/' . $product->getEntityId());

        $this->assertRedirect();
        $restProduct = $this->productRepository->get('simple_product_2');
        $this->_assertCompareListEquals([$product->getEntityId(), $restProduct->getEntityId()]);
    }

    /**
     * Test removing a product from compare list of a registered customer.
     *
     * @return void
     */
    public function testRemoveActionWithSession()
    {
        $this->_requireCustomerWithTwoProducts();
        $product = $this->productRepository->get('simple_product_1');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('catalog/product_compare/remove/product/' . $product->getEntityId());
        $secondProduct = $this->productRepository->get('simple_product_2');

        $this->assertSessionMessages(
            $this->equalTo(['You removed product Simple Product 1 Name from the comparison list.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([$secondProduct->getEntityId()]);
    }

    /**
     * Test getting a list of compared product.
     *
     * @return void
     */
    public function testIndexActionDisplay()
    {
        $this->_requireVisitorWithTwoProducts();

        $layout = $this->_objectManager->get(\Magento\Framework\View\LayoutInterface::class);
        $layout->setIsCacheable(false);

        $this->dispatch('catalog/product_compare/index');

        $responseBody = $this->getResponse()->getBody();

        $this->assertContains('Products Comparison List', $responseBody);

        $this->assertContains('simple_product_1', $responseBody);
        $this->assertContains('Simple Product 1 Name', $responseBody);
        $this->assertContains('Simple Product 1 Full Description', $responseBody);
        $this->assertContains('Simple Product 1 Short Description', $responseBody);
        $this->assertContains('$1,234.56', $responseBody);

        $this->assertContains('simple_product_2', $responseBody);
        $this->assertContains('Simple Product 2 Name', $responseBody);
        $this->assertContains('Simple Product 2 Full Description', $responseBody);
        $this->assertContains('Simple Product 2 Short Description', $responseBody);
        $this->assertContains('$987.65', $responseBody);
    }

    /**
     * Test clearing a list of compared products.
     *
     * @return void
     */
    public function testClearAction()
    {
        $this->_requireVisitorWithTwoProducts();

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('catalog/product_compare/clear');

        $this->assertSessionMessages(
            $this->equalTo(['You cleared the comparison list.']),
            MessageInterface::TYPE_SUCCESS
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([]);
    }

    /**
     * Test escaping a session message.
     *
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     * @return void
     */
    public function testRemoveActionProductNameXss()
    {
        $this->_prepareCompareListWithProductNameXss();
        $product = $this->productRepository->get('product-with-xss');
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('catalog/product_compare/remove/product/' . $product->getEntityId() . '?nocookie=1');

        $this->assertSessionMessages(
            $this->equalTo(
                ['You removed product &lt;script&gt;alert(&quot;xss&quot;);&lt;/script&gt; from the comparison list.']
            ),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Set product status disabled.
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function setProductDisabled($sku)
    {
        $product = $this->productRepository->get($sku);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
            ->save();

        return $product;
    }

    /**
     * Preparing compare list.
     *
     * @return void
     */
    protected function _prepareCompareListWithProductNameXss()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Visitor::class);
        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->save();
        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Compare\Item::class
        );
        $firstProductEntityId = $this->productRepository->get('product-with-xss')->getEntityId();
        $item->setVisitorId($visitor->getId())->setProductId($firstProductEntityId)->save();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Visitor::class
        )->load(
            $visitor->getId()
        );
    }

    /**
     * Preparing compare list.
     *
     * @return void
     */
    protected function _requireVisitorWithNoProducts()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Visitor::class);

        $visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Visitor::class
        )->load(
            $visitor->getId()
        );

        $this->_assertCompareListEquals([]);
    }

    /**
     * Preparing compare list.
     *
     * @return void
     */
    protected function _requireVisitorWithTwoProducts()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Visitor::class);
        $visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Compare\Item::class
        );
        $firstProductEntityId = $this->productRepository->get('simple_product_1')->getEntityId();
        $secondProductEntityId = $this->productRepository->get('simple_product_2')->getEntityId();
        $item->setVisitorId($visitor->getId())->setProductId($firstProductEntityId)->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Product\Compare\Item::class
        );
        $item->setVisitorId($visitor->getId())->setProductId($secondProductEntityId)->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Customer\Model\Visitor::class
        )->load(
            $visitor->getId()
        );

        $this->_assertCompareListEquals([$firstProductEntityId, $secondProductEntityId]);
    }

    /**
     * Preparing a compare list.
     *
     * @return void
     */
    protected function _requireCustomerWithTwoProducts()
    {
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Customer::class);
        /** @var \Magento\Customer\Model\Customer $customer */
        $customer
            ->setWebsiteId(1)
            ->setId(1)
            ->setEntityTypeId(1)
            ->setAttributeSetId(1)
            ->setEmail('customer@example.com')
            ->setPassword('password')
            ->setGroupId(1)
            ->setStoreId(1)
            ->setIsActive(1)
            ->setFirstname('Firstname')
            ->setLastname('Lastname')
            ->setDefaultBilling(1)
            ->setDefaultShipping(1);
        $customer->isObjectNew(true);
        $customer->save();

        /** @var $session \Magento\Customer\Model\Session */
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\Session::class);
        $session->setCustomerId(1);

        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Visitor::class);
        $visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->save();

        $firstProductEntityId = $this->productRepository->get('simple_product_1')->getEntityId();
        $secondProductEntityId = $this->productRepository->get('simple_product_2')->getEntityId();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product\Compare\Item::class);
        $item->setVisitorId($visitor->getId())
            ->setCustomerId(1)
            ->setProductId($firstProductEntityId)
            ->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product\Compare\Item::class);
        $item->setVisitorId($visitor->getId())
            ->setProductId($secondProductEntityId)
            ->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Customer\Model\Visitor::class)
            ->load($visitor->getId());

        $this->_assertCompareListEquals([$firstProductEntityId, $secondProductEntityId]);
    }

    /**
     * Assert that current visitor has exactly expected products in compare list
     *
     * @param array $expectedProductIds
     */
    protected function _assertCompareListEquals(array $expectedProductIds)
    {
        /** @var $compareItems \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection */
        $compareItems = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection::class
        );
        $compareItems->useProductItem(true);
        // important
        $compareItems->setVisitorId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                \Magento\Customer\Model\Visitor::class
            )->getId()
        );
        $actualProductIds = [];
        foreach ($compareItems as $compareItem) {
            /** @var $compareItem \Magento\Catalog\Model\Product\Compare\Item */
            $actualProductIds[] = $compareItem->getProductId();
        }
        $this->assertEquals($expectedProductIds, $actualProductIds, "Products in current visitor's compare list.");
    }
}
