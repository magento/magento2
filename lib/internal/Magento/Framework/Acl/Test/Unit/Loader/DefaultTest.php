<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Acl\Test\Unit\Loader;

class DefaultTest extends \PHPUnit\Framework\TestCase
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
        $aclMock = $this->createMock(\Magento\Framework\Acl::class);
        $aclMock->expects($this->never())->method('addRole');
        $aclMock->expects($this->never())->method('addResource');
        $aclMock->expects($this->never())->method('allow');
        $aclMock->expects($this->never())->method('deny');
        $this->_model->populateAcl($aclMock);
    }
}
