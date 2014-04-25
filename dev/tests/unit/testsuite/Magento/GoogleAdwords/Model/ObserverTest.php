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
namespace Magento\GoogleAdwords\Model;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_collectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_registryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventObserverMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eventMock;

    /**
     * @var \Magento\GoogleAdwords\Model\Observer
     */
    protected $_model;

    protected function setUp()
    {
        $this->_helperMock = $this->getMock('Magento\GoogleAdwords\Helper\Data', array(), array(), '', false);
        $this->_registryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', true);
        $this->_collectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Collection',
            array(),
            array(),
            '',
            false
        );
        $this->_eventObserverMock = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);
        $this->_eventMock = $this->getMock('Magento\Framework\Event', array('getOrderIds'), array(), '', false);

        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_model = $objectManager->getObject(
            'Magento\GoogleAdwords\Model\Observer',
            array(
                'helper' => $this->_helperMock,
                'collection' => $this->_collectionMock,
                'registry' => $this->_registryMock
            )
        );
    }

    public function dataProviderForDisabled()
    {
        return array(array(false, false), array(false, true), array(true, false));
    }

    /**
     * @param bool $isActive
     * @param bool $isDynamic
     * @dataProvider dataProviderForDisabled
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsDisabled($isActive, $isDynamic)
    {
        $this->_helperMock->expects(
            $this->once()
        )->method(
            'isGoogleAdwordsActive'
        )->will(
            $this->returnValue($isActive)
        );
        $this->_helperMock->expects($this->any())->method('isDynamicConversionValue')->will(
            $this->returnCallback(
                function () use ($isDynamic) {
                    return $isDynamic;
                }
            )
        );

        $this->_eventMock->expects($this->never())->method('getOrderIds');
        $this->assertSame($this->_model, $this->_model->setConversionValue($this->_eventObserverMock));
    }

    public function dataProviderForOrdersIds()
    {
        return array(array(array()), array(''));
    }

    /**
     * @param $ordersIds
     * @dataProvider dataProviderForOrdersIds
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsActiveWithoutOrdersIds($ordersIds)
    {
        $this->_helperMock->expects($this->once())->method('isGoogleAdwordsActive')->will($this->returnValue(true));
        $this->_helperMock->expects($this->once())->method('isDynamicConversionValue')->will($this->returnValue(true));
        $this->_eventMock->expects($this->once())->method('getOrderIds')->will($this->returnValue($ordersIds));
        $this->_eventObserverMock->expects(
            $this->once()
        )->method(
            'getEvent'
        )->will(
            $this->returnValue($this->_eventMock)
        );
        $this->_collectionMock->expects($this->never())->method('addFieldToFilter');

        $this->assertSame($this->_model, $this->_model->setConversionValue($this->_eventObserverMock));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testSetConversionValueWhenAdwordsActiveWithOrdersIds()
    {
        $ordersIds = array(1, 2, 3);
        $conversionValue = 0;
        $this->_helperMock->expects($this->once())->method('isGoogleAdwordsActive')->will($this->returnValue(true));
        $this->_helperMock->expects($this->once())->method('isDynamicConversionValue')->will($this->returnValue(true));
        $this->_eventMock->expects($this->once())->method('getOrderIds')->will($this->returnValue($ordersIds));
        $this->_eventObserverMock->expects(
            $this->once()
        )->method(
            'getEvent'
        )->will(
            $this->returnValue($this->_eventMock)
        );

        $iteratorMock = $this->getMock('Iterator');
        $this->_collectionMock->expects($this->any())->method('getIterator')->will($this->returnValue($iteratorMock));
        $this->_collectionMock->expects(
            $this->once()
        )->method(
            'addFieldToFilter'
        )->with(
            'entity_id',
            array('in' => $ordersIds)
        );
        $this->_registryMock->expects(
            $this->once()
        )->method(
            'register'
        )->with(
            \Magento\GoogleAdwords\Helper\Data::CONVERSION_VALUE_REGISTRY_NAME,
            $conversionValue
        );

        $this->assertSame($this->_model, $this->_model->setConversionValue($this->_eventObserverMock));
    }
}
