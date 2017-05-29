<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Loader;

class DefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Acl\Loader\DefaultLoader
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\Framework\Acl\Loader\DefaultLoader();
    }

    public function testPopulateAclDoesntChangeAclObject()
    {
        $aclMock = $this->getMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->never())->method('addRole');
        $aclMock->expects($this->never())->method('addResource');
        $aclMock->expects($this->never())->method('allow');
        $aclMock->expects($this->never())->method('deny');
        $this->_model->populateAcl($aclMock);
    }
}
