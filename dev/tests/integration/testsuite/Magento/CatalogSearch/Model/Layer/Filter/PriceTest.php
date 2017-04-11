<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Model\Layer\Filter;

/**
 * Test class for \Magento\CatalogSearch\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/_files/categories.php
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\CatalogSearch\Model\Layer\Filter\Price
     */
    protected $_model;

    protected function setUp()
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Catalog\Model\Category::class
        );
        $category->load(4);
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Model\Layer\Category::class);
        $layer->setCurrentCategory($category);
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create(\Magento\CatalogSearch\Model\Layer\Filter\Price::class, ['layer' => $layer]);
    }

    public function testApplyNothing()
    {
        $this->assertEmpty($this->_model->getData('price_range'));
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $this->_model->apply($request);

        $this->assertEmpty($this->_model->getData('price_range'));
    }

    public function testApplyInvalid()
    {
        $this->assertEmpty($this->_model->getData('price_range'));
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('price', 'non-numeric');
        $this->_model->apply($request);

        $this->assertEmpty($this->_model->getData('price_range'));
    }

    /**
     * @magentoConfigFixture current_store catalog/layered_navigation/price_range_calculation manual
     */
    public function testApplyManual()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get(\Magento\TestFramework\Request::class);
        $request->setParam('price', '10-20');
        $this->_model->apply($request);
    }

    public function testGetSetCustomerGroupId()
    {
        $this->assertEquals(
            \Magento\Customer\Model\GroupManagement::NOT_LOGGED_IN_ID,
            $this->_model->getCustomerGroupId()
        );

        $customerGroupId = 123;
        $this->_model->setCustomerGroupId($customerGroupId);

        $this->assertEquals($customerGroupId, $this->_model->getCustomerGroupId());
    }

    public function testGetSetCurrencyRate()
    {
        $this->assertEquals(1, $this->_model->getCurrencyRate());

        $currencyRate = 42;
        $this->_model->setCurrencyRate($currencyRate);

        $this->assertEquals($currencyRate, $this->_model->getCurrencyRate());
    }
}
