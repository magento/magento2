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
 * @category    Magento
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Model_Acl_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Model_Acl_Config
     */
    protected  $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_configMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cacheMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerMock;

    public function setUp()
    {
        $this->_readerMock = $this->getMock('Magento_Acl_Config_Reader', array(), array(), '', false);
        $this->_configMock = $this->getMock('Mage_Core_Model_Config', array(), array(), '', false);
        $this->_cacheMock  = $this->getMock('Mage_Core_Model_Cache', array(), array(), '', false);

        $this->_model = new Mage_Backend_Model_Acl_Config(array(
            'config' => $this->_configMock,
            'cache'  => $this->_cacheMock
        ));
    }

    public function testGetAclResourcesWhenCacheLoadCorruptedValue()
    {
        $originalAclResources = new DOMDocument();
        $originalAclResources->loadXML('<?xml version="1.0" encoding="utf-8"?><config><acl></acl></config>');

        $this->_configMock->expects($this->once())->method('getModelInstance')
            ->with($this->equalTo('Magento_Acl_Config_Reader'))
            ->will($this->returnValue($this->_readerMock));

        $this->_cacheMock->expects($this->exactly(2))->method('canUse')
            ->with($this->equalTo('config'))->will($this->returnValue(true));

        $this->_cacheMock->expects($this->once())->method('load')
            ->with($this->equalTo(Mage_Backend_Model_Acl_Config::CACHE_ID))
            ->will($this->returnValue(1234));

        $this->_cacheMock->expects($this->once())->method('save')
            ->with($this->equalTo($originalAclResources->saveXML()));

        $this->_readerMock->expects($this->once())->method('getAclResources')
            ->will($this->returnValue($originalAclResources));

        $this->_model->getAclResources();
    }

    public function testGetAclResourcesWithEnabledAndCleanedUpCache()
    {
        $originalAclResources = new DOMDocument();
        $originalAclResources->loadXML(
            '<?xml version="1.0" encoding="utf-8"?>'
            . '<config>'
                . '<acl>'
                    . '<resources>'
                        . '<resource id="res"></resource>'
                    . '</resources>'
                . '</acl>'
            . '</config>'
        );

        $this->_configMock->expects($this->once())->method('getModelInstance')
            ->with($this->equalTo('Magento_Acl_Config_Reader'))
            ->will($this->returnValue($this->_readerMock));

        $this->_cacheMock->expects($this->exactly(2))->method('canUse')
            ->with($this->equalTo('config'))->will($this->returnValue(true));

        $this->_cacheMock->expects($this->once())->method('load')
            ->with($this->equalTo(Mage_Backend_Model_Acl_Config::CACHE_ID))
            ->will($this->returnValue(null));

        $this->_cacheMock->expects($this->once())->method('save')
            ->with($this->equalTo($originalAclResources->saveXML()));

        $this->_readerMock->expects($this->once())->method('getAclResources')
            ->will($this->returnValue($originalAclResources));

        $aclResources = $this->_model->getAclResources();

        $this->assertInstanceOF('DOMNodeList', $aclResources);
        $this->assertEquals(1, $aclResources->length);
        $this->assertEquals('res', $aclResources->item(0)->getAttribute('id'));
    }

    public function testGetAclResourcesWithEnabledAndGeneratedCache()
    {
        $this->_configMock->expects($this->never())->method('getModelInstance');

        $this->_cacheMock->expects($this->exactly(2))->method('canUse')
            ->with($this->equalTo('config'))->will($this->returnValue(true));

        $this->_cacheMock->expects($this->exactly(2))->method('load')
            ->with($this->equalTo(Mage_Backend_Model_Acl_Config::CACHE_ID))
            ->will($this->returnValue('<?xml version="1.0" encoding="utf-8"?><config><acl></acl></config>'));

        $this->_cacheMock->expects($this->never())->method('save');
        $this->_readerMock->expects($this->never())->method('getAclResources');

        $firstCall = $this->_model->getAclResources();
        $secondCall = $this->_model->getAclResources();

        $this->assertNotEmpty($firstCall);
        $this->assertNotEmpty($secondCall);

        $this->assertEquals($firstCall, $secondCall);
    }

    public function testGetAclResourcesWithDisabledCache()
    {
        $aclResources = new DOMDocument();
        $aclResources->loadXML('<?xml version="1.0" encoding="utf-8"?><config><acl></acl></config>');

        $this->_configMock->expects($this->once())->method('getModelInstance')
            ->with($this->equalTo('Magento_Acl_Config_Reader'))
            ->will($this->returnValue($this->_readerMock));

        $this->_cacheMock->expects($this->exactly(4))->method('canUse')
            ->with($this->equalTo('config'))->will($this->returnValue(false));

        $this->_cacheMock->expects($this->never())->method('load');
        $this->_cacheMock->expects($this->never())->method('save');

        $this->_readerMock->expects($this->exactly(2))->method('getAclResources')
            ->will($this->returnValue($aclResources));

        $firstCall = $this->_model->getAclResources();
        $secondCall = $this->_model->getAclResources();

        $this->assertNotEmpty($firstCall);
        $this->assertNotEmpty($secondCall);
    }
}

