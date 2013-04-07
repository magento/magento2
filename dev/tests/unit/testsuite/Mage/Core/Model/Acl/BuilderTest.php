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
 * @package     Mage_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Acl_BuilderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclFactoryMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_aclMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_loaderPoolMock;

    /**
     * @var Mage_Core_Model_Acl_Builder
     */
    protected $_model;

    protected function setUp()
    {
        $this->_aclMock = new Magento_Acl();
        $this->_aclFactoryMock = $this->getMock('Magento_AclFactory', array(), array(), '', false);
        $this->_aclFactoryMock->expects($this->any())->method('create')->will($this->returnValue($this->_aclMock));
        $this->_loaderPoolMock = $this->getMock('Mage_Core_Model_Acl_LoaderPool', array(), array(), '', false);
        $this->_model = new Mage_Core_Model_Acl_Builder($this->_aclFactoryMock, $this->_loaderPoolMock);
    }

    protected function tearDown()
    {
        unset($this->_aclMock);
        unset($this->_aclFactoryMock);
        unset($this->_loaderPoolMock);
        unset($this->_model);
    }

    public function testGetAclUsesLoadersProvidedInConfigurationToPopulateAcl()
    {
        $defaultLoaderMock = $this->getMock('Magento_Acl_Loader_Default');
        $defaultLoaderMock->expects($this->exactly(3))
            ->method('populateAcl')
            ->with($this->equalTo($this->_aclMock));
        $this->_loaderPoolMock->expects($this->once())->method('getLoadersByArea')
            ->with('someArea')
            ->will($this->returnValue(
                new ArrayIterator(array(
                    $defaultLoaderMock, $defaultLoaderMock, $defaultLoaderMock
                ))
            ));

        $this->assertEquals($this->_aclMock, $this->_model->getAcl('someArea'));
    }

    /**
     * @expectedException LogicException
     */
    public function testGetAclRethrowsException()
    {
        $this->_loaderPoolMock->expects($this->once())
            ->method('getLoadersByArea')
            ->with('someArea')
            ->will($this->throwException(new InvalidArgumentException()));
        $this->_model->getAcl('someArea');
    }
}
