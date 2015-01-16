<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

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
        $this->areaListMock = $this->getMock('Magento\Framework\App\AreaList', ['getCodes'], [], '', false);
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
