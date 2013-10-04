<?php
/**
 * Test for \Magento\Acl\Loader\Resource
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
namespace Magento\Acl\Loader;

class ResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test for \Magento\Acl\Loader\Resource::populateAcl
     */
    public function testPopulateAclOnValidObjects()
    {
        /** @var $aclResource \Magento\Acl\Resource */
        $aclResource = $this->getMock('Magento\Acl\Resource', array(), array(), '', false);

        /** @var $acl \Magento\Acl */
        $acl = $this->getMock('Magento\Acl', array('addResource'), array(), '', false);
        $acl->expects($this->exactly(2))->method('addResource');
        $acl->expects($this->at(0))->method('addResource')->with($aclResource, null)->will($this->returnSelf());
        $acl->expects($this->at(1))->method('addResource')->with($aclResource, $aclResource)->will($this->returnSelf());

        /** @var $factoryObject \Magento\Core\Model\Config */
        $factoryObject = $this->getMock('Magento\Acl\ResourceFactory', array('createResource'), array(), '', false);
        $factoryObject->expects($this->any())->method('createResource')->will($this->returnValue($aclResource));

        /** @var $resourceProvider \Magento\Acl\Resource\ProviderInterface */
        $resourceProvider = $this->getMock('Magento\Acl\Resource\ProviderInterface');
        $resourceProvider->expects($this->once())
            ->method('getAclResources')
            ->will($this->returnValue(array(
                array(
                    'id' => 'parent_resource::id',
                    'title' => 'Parent Resource Title',
                    'sortOrder' => 10,
                    'children' => array(
                        array(
                            'id' => 'child_resource::id',
                            'title' => 'Child Resource Title',
                            'sortOrder' => 10,
                            'children' => array(),
                        ),
                    ),
                ),
            )));

        /** @var $loaderResource \Magento\Acl\Loader\Resource */
        $loaderResource = new \Magento\Acl\Loader\Resource($resourceProvider, $factoryObject);

        $loaderResource->populateAcl($acl);
    }
}
