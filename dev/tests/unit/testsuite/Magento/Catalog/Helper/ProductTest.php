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
namespace Magento\Catalog\Helper;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    protected function setUp()
    {
        $arguments = array(
            'reindexPriceIndexerData' => array(
                'byDataResult' => array('attribute'),
                'byDataChange' => array('attribute')
            )
        );

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_productHelper = $objectManager->getObject('Magento\Catalog\Helper\Product', $arguments);
    }

    /**
     * @param mixed $data
     * @param boolean $result
     * @dataProvider getData
     */
    public function testIsDataForPriceIndexerWasChanged($data, $result)
    {
        $this->assertEquals($this->_productHelper->isDataForPriceIndexerWasChanged($data), $result);
    }

    /**
     * Data provider for testIsDataForPriceIndexerWasChanged
     * @return array
     */
    public function getData()
    {
        $product1 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();

        $product2 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product2->expects(
            $this->once()
        )->method(
            'getData'
        )->with(
            $this->equalTo('attribute')
        )->will(
            $this->returnValue(true)
        );

        $product3 = $this->getMockBuilder('Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $product3->expects(
            $this->once()
        )->method(
            'dataHasChangedFor'
        )->with(
            $this->equalTo('attribute')
        )->will(
            $this->returnValue(true)
        );

        return array(
            array($product1, false),
            array($product2, true),
            array($product3, true),
            array(array('attribute' => ''), true),
            array(array('param' => ''), false),
            array('test', false)
        );
    }
}
