<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

/**
 * @magentoDataFixture Magento/Catalog/controllers/_files/products.php
 */
class CompareTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testAddAction()
    {
        $this->_requireVisitorWithNoProducts();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Data\Form\FormKey $formKey */
        $formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey');

        $this->dispatch('catalog/product_compare/add/product/1/form_key/' . $formKey->getFormKey() . '?nocookie=1');

        /** @var $messageManager \Magento\Framework\Message\Manager */
        $messageManager = $objectManager->get('Magento\Framework\Message\Manager');
        $this->assertInstanceOf(
            'Magento\Framework\Message\Success',
            $messageManager->getMessages()->getLastAddedMessage()
        );
        $this->assertContains(
            'Simple Product 1 Name',
            (string)$messageManager->getMessages()->getLastAddedMessage()->getText()
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([1]);
    }

    public function testIndexActionAddProducts()
    {
        $this->_requireVisitorWithNoProducts();

        $this->dispatch('catalog/product_compare/index/items/2');

        $this->assertRedirect($this->equalTo('http://localhost/index.php/catalog/product_compare/index/'));

        $this->_assertCompareListEquals([2]);
    }

    public function testRemoveAction()
    {
        $this->_requireVisitorWithTwoProducts();

        $this->dispatch('catalog/product_compare/remove/product/2');

        /** @var $messageManager \Magento\Framework\Message\Manager */
        $messageManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Message\Manager');
        $this->assertInstanceOf(
            'Magento\Framework\Message\Success',
            $messageManager->getMessages()->getLastAddedMessage()
        );
        $this->assertContains(
            'Simple Product 2 Name',
            (string)$messageManager->getMessages()->getLastAddedMessage()->getText()
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([1]);
    }

    public function testRemoveActionWithSession()
    {
        $this->_requireCustomerWithTwoProducts();

        $this->dispatch('catalog/product_compare/remove/product/1');

        /** @var $messageManager \Magento\Framework\Message\Manager */
        $messageManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Message\Manager');
        $this->assertInstanceOf(
            'Magento\Framework\Message\Success',
            $messageManager->getMessages()->getLastAddedMessage()
        );
        $this->assertContains('Simple Product 1 Name',
            (string)$messageManager->getMessages()->getLastAddedMessage()->getText());

        $this->assertRedirect();

        $this->_assertCompareListEquals([2]);
    }

    public function testIndexActionDisplay()
    {
        $this->_requireVisitorWithTwoProducts();

        $layout = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
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

    public function testClearAction()
    {
        $this->_requireVisitorWithTwoProducts();

        $this->dispatch('catalog/product_compare/clear');

        /** @var $messageManager \Magento\Framework\Message\Manager */
        $messageManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Message\Manager');
        $this->assertInstanceOf(
            'Magento\Framework\Message\Success',
            $messageManager->getMessages()->getLastAddedMessage()
        );

        $this->assertRedirect();

        $this->_assertCompareListEquals([]);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_xss.php
     */
    public function testRemoveActionProductNameXss()
    {
        $this->_prepareCompareListWithProductNameXss();
        $this->dispatch('catalog/product_compare/remove/product/1?nocookie=1');
        $messages = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Message\Manager'
        )->getMessages()->getItems();
        $isProductNamePresent = false;
        foreach ($messages as $message) {
            if (strpos($message->getText(), '&lt;script&gt;alert(&quot;xss&quot;);&lt;/script&gt;') !== false) {
                $isProductNamePresent = true;
            }
            $this->assertNotContains('<script>alert("xss");</script>', (string)$message->getText());
        }
        $this->assertTrue($isProductNamePresent, 'Product name was not found in session messages');
    }

    protected function _prepareCompareListWithProductNameXss()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Visitor');
        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Stdlib\DateTime');
        $visitor->setSessionId(md5(time()) . md5(microtime()))->setLastVisitAt($dateTime->now())->save();
        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Compare\Item'
        );
        $item->setVisitorId($visitor->getId())->setProductId(1)->save();
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Visitor'
        )->load(
            $visitor->getId()
        );
    }

    protected function _requireVisitorWithNoProducts()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Visitor');

        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Stdlib\DateTime');

        $visitor->setSessionId(md5(time()) . md5(microtime()))->setLastVisitAt($dateTime->now())->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Visitor'
        )->load(
            $visitor->getId()
        );

        $this->_assertCompareListEquals([]);
    }

    protected function _requireVisitorWithTwoProducts()
    {
        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Visitor');
        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Stdlib\DateTime');
        $visitor->setSessionId(md5(time()) . md5(microtime()))->setLastVisitAt($dateTime->now())->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Compare\Item'
        );
        $item->setVisitorId($visitor->getId())->setProductId(1)->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Product\Compare\Item'
        );
        $item->setVisitorId($visitor->getId())->setProductId(2)->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Customer\Model\Visitor'
        )->load(
            $visitor->getId()
        );

        $this->_assertCompareListEquals([1, 2]);
    }

    protected function _requireCustomerWithTwoProducts()
    {
        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Customer');
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
            ->get('Magento\Customer\Model\Session');
        $session->setCustomerId(1);

        /** @var $visitor \Magento\Customer\Model\Visitor */
        $visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Customer\Model\Visitor');
        /** @var \Magento\Framework\Stdlib\DateTime $dateTime */
        $dateTime = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Framework\Stdlib\DateTime');
        $visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt($dateTime->now())
            ->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product\Compare\Item');
        $item->setVisitorId($visitor->getId())
            ->setCustomerId(1)
            ->setProductId(1)
            ->save();

        /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
        $item = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Product\Compare\Item');
        $item->setVisitorId($visitor->getId())
            ->setProductId(2)
            ->save();

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Customer\Model\Visitor')
            ->load($visitor->getId());

        $this->_assertCompareListEquals([1, 2]);
    }

    /**
     * Assert that current visitor has exactly expected products in compare list
     *
     * @param array $expectedProductIds
     */
    protected function _assertCompareListEquals(array $expectedProductIds)
    {
        /** @var $compareItems \Magento\Catalog\Model\Resource\Product\Compare\Item\Collection */
        $compareItems = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Resource\Product\Compare\Item\Collection'
        );
        $compareItems->useProductItem(true);
        // important
        $compareItems->setVisitorId(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Customer\Model\Visitor')->getId()
        );
        $actualProductIds = [];
        foreach ($compareItems as $compareItem) {
            /** @var $compareItem \Magento\Catalog\Model\Product\Compare\Item */
            $actualProductIds[] = $compareItem->getProductId();
        }
        $this->assertEquals($expectedProductIds, $actualProductIds, "Products in current visitor's compare list.");
    }
}
