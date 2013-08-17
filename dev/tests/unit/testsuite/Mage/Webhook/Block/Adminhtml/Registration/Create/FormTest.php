<?php
/**
 * Mage_Webhook_Block_Adminhtml_Registration_Create_Form
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
class Mage_Webhook_Block_Adminhtml_Registration_Create_FormTest extends Magento_Test_Block_Adminhtml
{
    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_formMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_formFactoryMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_coreHelperMock;

    /** @var  PHPUnit_Framework_MockObject_MockObject */
    private $_dataFormMock;

    /** @var  Mage_Core_Model_Registry */
    private $_registry;

    /** @var  string[] */
    private $_actualIds;

    public function setUp()
    {
        parent::setUp();
        $this->_registry = new Mage_Core_Model_Registry();
        $this->_coreHelperMock = $this->_makeMock('Mage_Core_Helper_Data');
        $this->_formFactoryMock = $this->_makeMock('Varien_Data_Form_Factory');

        $this->_dataFormMock = $this->_makeMock('Varien_Data_Form');
        $this->_setStub($this->_formFactoryMock, 'create', $this->_dataFormMock);

        $selectMock = $this->_makeMock('Varien_Db_Select');
        $collectionMock = $this->_makeMock('Varien_Data_Collection_Db');
        $this->_setStub($collectionMock, 'getSelect', $selectMock);

        $arguments = array(
            $this->_coreHelperMock,
            $this->_registry,
            $this->_context,
            $this->_formFactoryMock
        );

        $methods = array(
            'getId',
            'sortColumnsByOrder',
            '_prepareMassactionBlock',
            '_prepareFilterButtons',
            'getChildBlock',
            '_toHtml',
            '_saveCache',
            '_afterToHtml',
            'addColumn'

        );

        $this->_formMock = $this->getMock(
            'Mage_Webhook_Block_Adminhtml_Registration_Create_Form',
            $methods,
            $arguments);
    }

    public function testPrepareColumns()
    {
        $columnsSetMock = $this->_makeMock('Mage_Backend_Block_Widget_Grid_ColumnSet');
        $this->_setStub($this->_formMock, 'getChildBlock', $columnsSetMock);

        $this->_dataFormMock->expects($this->exactly(4))
            ->method('addField')
            ->will($this->returnCallback(array($this, 'logAddFieldArguments')));

        // Intended to call _prepareColumns
        $this->_formMock->toHtml();

        $expectedIds = array('company', 'email', 'apikey', 'apisecret');
        $this->assertEquals($expectedIds, $this->_actualIds);
    }

    /**
     * Logs addField's id argument for later verification
     *
     * @param string $actualId
     */
    public function logAddFieldArguments($actualId)
    {
        $this->_actualIds[] = $actualId;
    }
}