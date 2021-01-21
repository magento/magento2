<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\AreaList;
use Magento\Framework\Config\Scope;
use PHPUnit\Framework\MockObject\MockObject;

class ScopeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Scope
     */
    private $model;

    /**
     * @var MockObject|AreaList
     */
    private $areaListMock;

    protected function setUp(): void
    {
        $this->areaListMock = $this->createPartialMock(AreaList::class, ['getCodes']);
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
        $expectedBalances = ['primary', 'global', 'test_scope'];
        $this->areaListMock->expects($this->once())
            ->method('getCodes')
            ->willReturn(['test_scope']);
        $this->assertEquals($expectedBalances, $this->model->getAllScopes());
    }
}
