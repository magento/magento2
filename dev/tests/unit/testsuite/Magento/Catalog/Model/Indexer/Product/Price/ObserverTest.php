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
namespace Magento\Catalog\Model\Indexer\Product\Price;

class ObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Observer
     */
    protected $_model;

    /**
     * @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_storeManagerMock;

    /**
     * @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_dateTimeMock;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_localeDateMock;

    /**
     * @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_eavConfigMock;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_priceProcessorMock;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->_storeManagerMock = $this->getMock(
            'Magento\Framework\StoreManagerInterface',
            array(),
            array(),
            '',
            false
        );
        $this->_resourceMock = $this->getMock('Magento\Framework\App\Resource', array(), array(), '', false);
        $this->_dateTimeMock = $this->getMock('Magento\Framework\Stdlib\DateTime', array(), array(), '', false);
        $this->_localeDateMock = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->_eavConfigMock = $this->getMock('Magento\Eav\Model\Config', array(), array(), '', false);
        $this->_priceProcessorMock = $this->getMock(
            'Magento\Catalog\Model\Indexer\Product\Price\Processor',
            array(),
            array(),
            '',
            false
        );

        $this->_model = $this->_objectManager->getObject(
            '\Magento\Catalog\Model\Indexer\Product\Price\Observer',
            array(
                'storeManager' => $this->_storeManagerMock,
                'resource' => $this->_resourceMock,
                'dateTime' => $this->_dateTimeMock,
                'localeDate' => $this->_localeDateMock,
                'eavConfig' => $this->_eavConfigMock,
                'processor' => $this->_priceProcessorMock
            )
        );
    }

    public function testRefreshSpecialPrices()
    {
        $idsToProcess = array(1, 2, 3);

        $selectMock = $this->getMock('Magento\Framework\DB\Select', array(), array(), '', false);
        $selectMock->expects($this->any())->method('from')->will($this->returnSelf());
        $selectMock->expects($this->any())->method('where')->will($this->returnSelf());

        $connectionMock = $this->getMock('Magento\Framework\DB\Adapter\AdapterInterface', array(), array(), '', false);
        $connectionMock->expects($this->any())->method('select')->will($this->returnValue($selectMock));
        $connectionMock->expects(
            $this->any()
        )->method(
            'fetchCol'
        )->with(
            $selectMock,
            array('entity_id')
        )->will(
            $this->returnValue($idsToProcess)
        );
        $this->_resourceMock->expects(
            $this->once()
        )
        ->method(
            'getConnection'
        )
        ->with(
            'write'
        )
        ->will(
            $this->returnValue($connectionMock)
        );


        $storeMock = $this->getMock('\Magento\Store\Model\Store', array(), array(), '', false);
        $storeMock->expects($this->any())->method('getId')->will($this->returnValue(1));


        $this->_storeManagerMock->expects(
            $this->once()
        )->method(
            'getStores'
        )->with(
            true
        )->will(
            $this->returnValue(array($storeMock))
        );

        $this->_localeDateMock->expects(
            $this->once()
        )->method(
            'scopeTimeStamp'
        )->with(
            $storeMock
        )->will(
            $this->returnValue(32000)
        );

        $indexerMock = $this->getMock('Magento\Indexer\Model\Indexer', array(), array(), '', false);
        $indexerMock->expects($this->exactly(2))->method('reindexList');

        $this->_priceProcessorMock->expects(
            $this->exactly(2)
        )->method(
            'getIndexer'
        )->will(
            $this->returnValue($indexerMock)
        );

        $attributeMock = $this->getMockForAbstractClass(
            'Magento\Eav\Model\Entity\Attribute\AbstractAttribute',
            array(),
            '',
            false,
            true,
            true,
            array('__wakeup', 'getAttributeId')
        );
        $attributeMock->expects($this->any())->method('getAttributeId')->will($this->returnValue(1));

        $this->_eavConfigMock->expects($this->any())->method('getAttribute')->will($this->returnValue($attributeMock));

        $this->_model->refreshSpecialPrices();
    }
}
