<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend\Grid;

use Magento\Framework\AuthorizationInterface;
use Magento\Indexer\Block\Backend\Grid\ItemsUpdater;
use PHPUnit\Framework\TestCase;

class ItemsUpdaterTest extends TestCase
{
    /**
     * @param bool $argument
     * @dataProvider updateDataProvider
     */
    public function testUpdate($argument)
    {
        $params = ['change_mode_onthefly' => 1, 'change_mode_changelog' => 2];

        $auth = $this->getMockBuilder(AuthorizationInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
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
