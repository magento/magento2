<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Layer\Filter\Price;

/**
 * Test class for \Magento\Catalog\Model\Layer\Filter\Price.
 *
 * @magentoDataFixture Magento/Catalog/Model/Layer/Filter/Price/_files/products_advanced.php
 */
class AlgorithmAdvancedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Algorithm model
     *
     * @var \Magento\Catalog\Model\Layer\Filter\Price\Algorithm
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Layer\Filter\Price\Algorithm'
        );
    }

    /**
     * Prepare price filter model
     *
     * @param \Magento\TestFramework\Request|null $request
     */
    protected function _prepareFilter($request = null)
    {
        /** @var $layer \Magento\Catalog\Model\Layer */
        $layer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Category');
        $layer->setCurrentCategory(4);
        $layer->setState(
            \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Layer\State')
        );
        /** @var $filter \Magento\Catalog\Model\Layer\Filter\Price */
        $filter = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->create('Magento\Catalog\Model\Layer\Filter\Price', array('layer' => $layer));
        $filter->setLayer($layer)->setAttributeModel(new \Magento\Framework\Object(array('attribute_code' => 'price')));
        if (!is_null($request)) {
            $filter->apply(
                $request,
                \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
                    'Magento\Framework\View\LayoutInterface'
                )->createBlock(
                    'Magento\Framework\View\Element\Text'
                )
            );
            $interval = $filter->getInterval();
            if ($interval) {
                $this->_model->setLimits($interval[0], $interval[1]);
            }
        }
        $collection = $layer->getProductCollection();
        $this->_model->setPricesModel(
            $filter
        )->setStatistics(
            $collection->getMinPrice(),
            $collection->getMaxPrice(),
            $collection->getPriceStandardDeviation(),
            $collection->getSize()
        );
    }

    public function testWithoutLimits()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setParam('price', null);
        $this->_prepareFilter();
        $this->assertEquals(
            array(
                0 => array('from' => 0, 'to' => 20, 'count' => 3),
                1 => array('from' => 20, 'to' => '', 'count' => 4)
            ),
            $this->_model->calculateSeparators()
        );
    }

    public function testWithLimits()
    {
        $this->markTestIncomplete('Bug MAGE-6561');
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $request \Magento\TestFramework\Request */
        $request = $objectManager->get('Magento\TestFramework\Request');
        $request->setParam('price', '10-100');
        $this->_prepareFilter($request);
        $this->assertEquals(
            array(
                0 => array('from' => 10, 'to' => 20, 'count' => 2),
                1 => array('from' => 20, 'to' => 100, 'count' => 2)
            ),
            $this->_model->calculateSeparators()
        );
    }
}
