<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Compare;

class ListCompareTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $_model;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_visitor;

    /** @var \Magento\Customer\Model\Session */
    protected $_session;

    protected function setUp()
    {
        /** @var $session \Magento\Customer\Model\Session */
        $this->_session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Customer\Model\Session::class);
        $this->_visitor = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Customer\Model\Visitor::class);
        $this->_visitor->setSessionId(md5(time()) . md5(microtime()))
            ->setLastVisitAt((new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT))
            ->save();
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product\Compare\ListCompare::class, ['customerVisitor' => $this->_visitor]);
    }

    protected function tearDown()
    {
        $this->_session->setCustomerId(null);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddProductWithSession()
    {
        $this->_session->setCustomerId(1);
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product::class)
            ->load(1);
        /** @var $product2 \Magento\Catalog\Model\Product */
        $product2 = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product::class)
            ->load(6);
        $products = [$product->getId(), $product2->getId()];
        $this->_model->addProducts($products);

        $this->assertTrue($this->_model->hasItems(1, $this->_visitor->getId()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddProductWithSessionNeg()
    {
        $this->_session->setCustomerId(1);
        $products = ['none', 99];
        $this->_model->addProducts($products);

        $this->assertFalse($this->_model->hasItems(1, $this->_visitor->getId()));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testAddProductWithoutSession()
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\Catalog\Model\Product::class)
            ->load(1);
        $this->_model->addProduct($product);
        $this->assertFalse($this->_model->hasItems(1, $this->_visitor->getId()));
        $this->assertTrue($this->_model->hasItems(0, $this->_visitor->getId()));
    }
}
