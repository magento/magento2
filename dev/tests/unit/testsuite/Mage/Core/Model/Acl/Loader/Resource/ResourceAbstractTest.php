<?php
/**
 * Test for Mage_Core_Model_Acl_Loader_Resource_ResourceAbstract
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Core_Model_Acl_Loader_Resource_ResourceAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for Mage_Core_Model_Acl_Loader_Resource_ResourceAbstract::populateAcl
     */
    public function testPopulateAclOnValidObjects()
    {
        /** @var $aclResource Magento_Acl_Resource */
        $aclResource = $this->getMock('Magento_Acl_Resource', array(), array(), '', false);

        /** @var $acl Magento_Acl */
        $acl = $this->getMock('Magento_Acl', array('addResource'), array(), '', false);
        $acl->expects($this->exactly(3))->method('addResource');
        $acl->expects($this->at(0))->method('addResource')->with($aclResource, null)->will($this->returnSelf());
        $acl->expects($this->at(1))->method('addResource')->with($aclResource, $aclResource)->will($this->returnSelf());
        $acl->expects($this->at(2))->method('addResource')->with($aclResource, $aclResource)->will($this->returnSelf());

        /** @var $factoryObject Mage_Core_Model_Config */
        $factoryObject = $this->getMock('Magento_Acl_ResourceFactory', array('createResource'), array(), '', false);
        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $configObject Mage_Core_Model_Acl_Config_ConfigInterface */
        $configObject = $this->getMock('Mage_Core_Model_Acl_Config_ConfigInterface',
            array('getAclResources'), array(), '', false);
        $configObject->expects($this->once())->method('getAclResources')
            ->will($this->returnCallback(array($this, 'getResourceNodeList')));

        /** @var $loaderResource Mage_Core_Model_Acl_Loader_Resource_ResourceAbstract */
        $loaderResource = $this->getMockForAbstractClass('Mage_Core_Model_Acl_Loader_Resource_ResourceAbstract',
            array($configObject, $factoryObject));

        $loaderResource->populateAcl($acl);
    }

    /**
     * Get Resources DOMNodeList from fixture
     *
     * @return DOMNodeList
     */
    public function getResourceNodeList()
    {
        $aclResources = new DOMDocument();
        $aclResources->load(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
            . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '_files'
            . DIRECTORY_SEPARATOR . 'acl_resources.xml');
        $xpath = new DOMXPath($aclResources);
        return $xpath->query('/config/resources/*');
    }
}
