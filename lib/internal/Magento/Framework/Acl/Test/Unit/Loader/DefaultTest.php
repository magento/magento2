<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Test\Unit\Loader;

use Magento\Framework\Acl;
use Magento\Framework\Acl\Loader\DefaultLoader;
use PHPUnit\Framework\TestCase;

class DefaultTest extends TestCase
{
    /**
     * @var DefaultLoader
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new DefaultLoader();
    }

    public function testPopulateAclDoesntChangeAclObject()
    {
        $aclMock = $this->createMock(Acl::class);
        $aclMock->expects($this->never())->method('addRole');
        $aclMock->expects($this->never())->method('addResource');
        $aclMock->expects($this->never())->method('allow');
        $aclMock->expects($this->never())->method('deny');
        $this->_model->populateAcl($aclMock);
    }
}
