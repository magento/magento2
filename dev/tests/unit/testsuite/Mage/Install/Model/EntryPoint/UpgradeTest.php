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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Install_Model_EntryPoint_UpgradeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexer;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_config = $this->getMock('Mage_Core_Model_Config_Primary', array('getParam'), array(), '', false);

        $cacheFrontend = $this->getMockForAbstractClass('Magento_Cache_FrontendInterface');
        $cacheFrontend->expects($this->once())->method('clean')->with('all', array());
        $cacheFrontendPool = $this->getMock(
            'Mage_Core_Model_Cache_Frontend_Pool', array('valid', 'current'), array(
                $this->_config,
                $this->getMock('Mage_Core_Model_Cache_Frontend_Factory', array(), array(), '', false),
            )
        );
        $cacheFrontendPool->expects($this->at(0))->method('valid')->will($this->returnValue(true));
        $cacheFrontendPool->expects($this->once())->method('current')->will($this->returnValue($cacheFrontend));

        $update = $this->getMock('Mage_Core_Model_Db_Updater', array('updateScheme', 'updateData'), array(), '', false);
        $update->expects($this->once())->method('updateScheme');
        $update->expects($this->once())->method('updateData');

        $this->_indexer = $this->getMock(
            'Mage_Index_Model_Indexer', array('reindexAll', 'reindexRequired'), array(), '', false
        );

        $this->_objectManager = $this->getMock('Magento_ObjectManager');
        $this->_objectManager->expects($this->any())->method('get')->will($this->returnValueMap(array(
            array('Mage_Core_Model_Cache_Frontend_Pool', $cacheFrontendPool),
            array('Mage_Core_Model_Db_Updater', $update),
            array('Mage_Core_Model_Config_Primary', $this->_config),
            array('Mage_Index_Model_Indexer', $this->_indexer),
        )));
    }

    /**
     * @param string $reindexMode
     * @param int $reindexAllCount
     * @param int $reindexReqCount
     * @dataProvider processRequestDataProvider
     */
    public function testProcessRequest($reindexMode, $reindexAllCount, $reindexReqCount)
    {
        $this->_indexer->expects($this->exactly($reindexAllCount))->method('reindexAll');
        $this->_indexer->expects($this->exactly($reindexReqCount))->method('reindexRequired');
        $this->_config->expects($this->once())->method('getParam')->with(Mage_Install_Model_EntryPoint_Upgrade::REINDEX)
            ->will($this->returnValue($reindexMode));
        Mage::reset(); // hack to reset object manager if it happens to be set in this class already
        $upgrade = new Mage_Install_Model_EntryPoint_Upgrade($this->_config, $this->_objectManager);
        $upgrade->processRequest();
    }

    public function processRequestDataProvider()
    {
        return array(
            'no reindex'       => array('', 0, 0),
            'reindex all'      => array(Mage_Install_Model_EntryPoint_Upgrade::REINDEX_ALL, 1, 0),
            'reindex required' => array(Mage_Install_Model_EntryPoint_Upgrade::REINDEX_INVALID, 0, 1),
        );
    }
}
