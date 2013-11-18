<?php
/**
 * Test class for \Magento\Webapi\Model\Authorization\Loader\Resource
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
namespace Magento\Webapi\Model\Authorization\Loader;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Model\Authorization\Loader\Resource
     */
    protected $_model;

    /**
     * @var \Magento\Acl
     */
    protected $_acl;

    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_helper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_resourceProvider;

    /**
     * Set up before test.
     */
    protected function setUp()
    {
        $fixturePath = __DIR__ . '/../../_files/';
        $this->_helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $resource = new \Magento\Acl\Resource('test resource');

        /** @var $resourceFactory \Magento\Acl\ResourceFactory */
        $resourceFactory = $this->getMock('Magento\Acl\ResourceFactory',
            array('createResource'), array(), '', false);
        $resourceFactory->expects($this->any())
            ->method('createResource')
            ->will($this->returnValue($resource));

        $this->_resourceProvider = $this->getMock('Magento\Webapi\Model\Acl\Resource\ProviderInterface');
        $this->_resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue(include $fixturePath . 'acl.php'));

        $this->_model = $this->_helper->getObject('Magento\Webapi\Model\Authorization\Loader\Resource', array(
            'resourceFactory' => $resourceFactory,
            'resourceProvider' => $this->_resourceProvider,
        ));

        $this->_acl = $this->getMock(
            'Magento\Acl', array('has', 'addResource', 'deny', 'getResources'), array(), '', false
        );
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Resource::populateAcl.
     */
    public function testPopulateAcl()
    {
        $aclFilePath = __DIR__ . DIRECTORY_SEPARATOR .  '..' . DIRECTORY_SEPARATOR .  '..'
            . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'acl.xml';
        $aclDom = new \DOMDocument();
        $aclDom->loadXML(file_get_contents($aclFilePath));
        $domConverter = new \Magento\Webapi\Model\Acl\Resource\Config\Converter\Dom();
        $aclResourceConfig = $domConverter->convert($aclDom);

        $this->_resourceProvider->expects($this->once())
            ->method('getAclVirtualResources')
            ->will($this->returnValue($aclResourceConfig['config']['mapping']));

        $this->_acl->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array('customer/get', 'customer/create')));
        $this->_acl->expects($this->exactly(2))
            ->method('deny')
            ->with(null, $this->logicalOr('customer/get', 'customer/create'));
        $this->_acl->expects($this->exactly(2))
            ->method('has')
            ->with($this->logicalOr('customer/get', 'customer/list'))
            ->will($this->returnValueMap(array(
                array('customer/get', true),
                array('customer/list', false)
            )));
        $this->_acl->expects($this->exactly(7))
            ->method('addResource');

        $this->_model->populateAcl($this->_acl);
    }

    /**
     * Test for \Magento\Webapi\Model\Authorization\Loader\Resource::populateAcl with invalid Virtual resources DOM.
     */
    public function testPopulateAclWithInvalidDOM()
    {
        $this->_resourceProvider->expects($this->once())
            ->method('getAclVirtualResources')
            ->will($this->returnValue(array()));

        $this->_acl->expects($this->once())
            ->method('getResources')
            ->will($this->returnValue(array('customer/get', 'customer/list')));
        $this->_acl->expects($this->exactly(2))
            ->method('deny')
            ->with(null, $this->logicalOr('customer/get', 'customer/list'));

        $this->_model->populateAcl($this->_acl);
    }
}
