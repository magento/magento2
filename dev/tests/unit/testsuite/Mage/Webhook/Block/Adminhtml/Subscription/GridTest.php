<?php
/**
 * Mage_Webhook_Block_Adminhtml_Subscription_Grid
 *
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
 * @category    Mage
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Subscription_GridTest extends Magento_Test_Block_Adminhtml
{
    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_subxConfigMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_subxFactoryMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_subscriptionMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_gridMock;

    /** @var  string[] */
    private $_actualIds;

    public function setUp()
    {
        parent::setUp();

        $this->_subxConfigMock = $this->_makeMock('Mage_Webhook_Model_Subscription_Config');
        $this->_subxFactoryMock = $this->_makeMock('Mage_Webhook_Model_Subscription_Factory');
        $this->_subscriptionMock = $this->_makeMock('Mage_Webhook_Model_Subscription');
        $storeManagerMock = $this->_makeMock('Mage_Core_Model_StoreManagerInterface');
        $urlMock = $this->_makeMock('Mage_Core_Model_Url');

        $this->_setStub($this->_subxFactoryMock, 'create', $this->_subscriptionMock);


        // Arguments to pass to constructor
        $arguments = array(
            $this->_subxConfigMock,
            $this->_subxFactoryMock,
            $this->_context,
            $storeManagerMock,
            $urlMock
        );

        // Parent methods to be mocked out, not tested
        $methods = array(
            'getId',
            'sortColumnsByOrder',
            '_prepareMassactionBlock',
            '_prepareFilterButtons',
            'getChildBlock',
            '_toHtml',
            '_saveCache',
            '_afterToHtml',
            'addColumn',
            '_construct'

        );
        $this->_gridMock =  $this->getMock('Mage_Webhook_Block_Adminhtml_Subscription_Grid', $methods, $arguments);
    }

    public function testGetRowUrl()
    {
        $url = 'uniform resource locater';
        $this->_setStub($this->_urlMock, 'getUrl', $url, $this->once())
            ->with('*/*/edit', array('id' => 'row_id'));
        $row = $this->_makeMock('Mage_Catalog_Model_Product');
        $this->_setStub($row, 'getId', 'row_id', $this->once());

        $this->assertEquals($url, $this->_gridMock->getRowUrl($row));
    }

    public function testPrepareCollection()
    {
        $this->_gridMock->setData('dataSource', 'prepared_collection');
        $subscriptionMock = $this->_makeMock('Mage_Webhook_Model_Subscription');
        $this->_setStub($this->_subxFactoryMock, 'create', $subscriptionMock,
            $this->once());

        $this->_subxConfigMock->expects($this->once())
            ->method('updateSubscriptionCollection');

        $this->_gridMock->getPreparedCollection();
    }

    public function testPrepareColumns()
    {
        $selectMock = $this->_makeMock('Varien_Db_Select');

        $collectionMock = $this->_makeMock('Varien_Data_Collection_Db');
        $this->_setStub($collectionMock, 'getSelect', $selectMock);
        $this->_gridMock->setCollection($collectionMock);
        $this->_gridMock->expects($this->exactly(6))
            ->method('addColumn')
            ->will($this->returnCallback(array($this, 'logAddColumArguments')));
        $columnsSetMock = $this->_makeMock('Mage_Backend_Block_Widget_Grid_ColumnSet');
        $this->_setStub($this->_gridMock, 'getChildBlock', $columnsSetMock);
        $this->_gridMock->toHtml();

        $expectedIds = array('id', 'name', 'version', 'endpoint_url', 'status', 'action');
        $this->assertEquals($expectedIds, $this->_actualIds);

    }

    /**
     * Logs addColumns's id argument for later verification
     *
     * @param string $actualId
     */
    public function logAddColumArguments($actualId)
    {
        $this->_actualIds[] = $actualId;
    }
}