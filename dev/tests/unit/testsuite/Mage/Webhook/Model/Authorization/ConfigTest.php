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
 * @package     Mage_Webhook
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_Webhook_Model_Authorization_Config
 */
class Mage_Webhook_Model_Authorization_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Webhook_Model_Authorization_Config
     */
    protected $_model;

    /**
     * @var Magento_Acl_Config_Reader
     */
    protected $_configReader;

    /**
     * @var Mage_Webhook_Model_Authorization_Config_Reader_Factory|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_readerFactory;

    /**
     * @var Mage_Core_Model_Config|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /** @var Mage_Core_Model_Config_Modules_Reader */
    protected $_moduleReader;

    /**
     * Set up before test
     */
    protected function setUp()
    {
        $helper = new Magento_Test_Helper_ObjectManager($this);

        $this->_config = $this->getMockBuilder('Mage_Core_Model_Config_Modules_Reader')
            ->disableOriginalConstructor()
            ->setMethods(array('getModuleConfigurationFiles'))
            ->getMock();

        $this->_readerFactory = $this->getMockBuilder('Mage_Webhook_Model_Authorization_Config_Reader_Factory')
            ->disableOriginalConstructor()
            ->setMethods(array('createReader'))
            ->getMock();

        $this->_configReader = $this->getMock('Magento_Acl_Config_Reader',
            array('getAclResources'), array(), '', false);

        $this->_model = $helper->getObject('Mage_Webhook_Model_Authorization_Config', array(
            'moduleReader' => $this->_config,
            'readerFactory' => $this->_readerFactory
        ));

        $this->_config->expects($this->any())
            ->method('getModuleConfigurationFiles')
            ->will($this->returnValue(array()));

        $this->_readerFactory->expects($this->any())
            ->method('createReader')
            ->will($this->returnValue($this->_configReader));
    }

    public static function getParentFromTopicProvider()
    {
        return array(
            array('customer/updated','customer/get'),
            array('catalog/updated','catalog/get'),
            array('customer/created','customer/get'),
            array('catalog/created','catalog/get'),
            array('customer/deleted','customer/get'),
            array('catalog/deleted','catalog/get')
        );

    }
    /**
     * test for Mage_Webhook_Model_Authorization_Config::testGetParentFromTopic
     * @dataProvider getParentFromTopicProvider
     */
    public function testGetParentFromTopic($topic, $expectedParent)
    {
        $aclResources = new DOMDocument();
        $aclFile = __DIR__ . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl2.xml';
        $aclResources->load($aclFile);

        $this->_configReader->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue($aclResources));

        $actualParent = $this->_model->getParentFromTopic($topic);
        $this->assertEquals($expectedParent, $actualParent);
    }
}
