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
namespace Magento\Bundle\Model\Product;

class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $_model;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectHelper->getObject(
            'Magento\Bundle\Model\Product\Type',
            array(
                'productFactory' => $this->getMock('Magento\Catalog\Model\ProductFactory'),
                'bundleModelSelection' => $this->getMock('Magento\Bundle\Model\SelectionFactory'),
                'bundleFactory' => $this->getMock('Magento\Bundle\Model\Resource\BundleFactory'),
                'bundleCollection' => $this->getMock('Magento\Bundle\Model\Resource\Selection\CollectionFactory'),
                'bundleOption' => $this->getMock('Magento\Bundle\Model\OptionFactory')
            )
        );
    }

    public function testHasWeightTrue()
    {
        $this->assertTrue($this->_model->hasWeight(), 'This product has not weight, but it should');
    }

    public function testGetIdentities()
    {
        $identities = array('id1', 'id2');
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $optionMock = $this->getMock(
            '\Magento\Bundle\Model\Option',
            array('getSelections', '__wakeup'),
            array(),
            '',
            false
        );
        $optionCollectionMock = $this->getMock(
            'Magento\Bundle\Model\Resource\Option\Collection',
            array(),
            array(),
            '',
            false
        );
        $cacheKey = '_cache_instance_options_collection';
        $productMock->expects($this->once())
            ->method('getIdentities')
            ->will($this->returnValue($identities));
        $productMock->expects($this->once())
            ->method('hasData')
            ->with($cacheKey)
            ->will($this->returnValue(true));
        $productMock->expects($this->once())
            ->method('getData')
            ->with($cacheKey)
            ->will($this->returnValue($optionCollectionMock));
        $optionCollectionMock
            ->expects($this->once())
            ->method('getItems')
            ->will($this->returnValue(array($optionMock)));
        $optionMock
            ->expects($this->exactly(2))
            ->method('getSelections')
            ->will($this->returnValue(array($productMock)));
        $this->assertEquals($identities, $this->_model->getIdentities($productMock));
    }
}
