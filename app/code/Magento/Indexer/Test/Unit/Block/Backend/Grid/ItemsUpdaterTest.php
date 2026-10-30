<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend\Grid;

use Magento\Framework\AuthorizationInterface;
use Magento\Indexer\Block\Backend\Grid\ItemsUpdater;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ItemsUpdaterTest extends TestCase
{
    /**
     * @param bool $argument
     */
    #[DataProvider('updateDataProvider')]
    public function testUpdate($argument)
    {
        $params = ['change_mode_onthefly' => 1, 'change_mode_changelog' => 2];

        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Indexer::changeMode')
            ->willReturn($argument);

        $model = new ItemsUpdater($auth);
        $params = $model->update($params);
        $this->assertEquals(
            $argument,
            (isset($params['change_mode_onthefly']) && isset($params['change_mode_changelog']))
        );
    }

    /**
     * @return array
     */
    public static function updateDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
