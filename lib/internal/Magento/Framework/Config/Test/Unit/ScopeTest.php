<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit;

use \Magento\Framework\Config\Scope;

class ScopeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Config\Scope
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\AreaList
     */
    protected $areaListMock;

    protected function setUp()
    {
        $this->areaListMock = $this->getMock(\Magento\Framework\App\AreaList::class, ['getCodes'], [], '', false);
        $this->model = new Scope($this->areaListMock);
    }

    public function testScopeSetGet()
    {
        $scopeName = 'test_scope';
        $this->model->setCurrentScope($scopeName);
        $this->assertEquals($scopeName, $this->model->getCurrentScope());
    }

    public function testGetAllScopes()
    {
        $expectedBalances = ['primary', 'test_scope'];
        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->will($this->returnValue(['test_scope']));
        $this->assertEquals($expectedBalances, $this->model->getAllScopes());
    }
}
