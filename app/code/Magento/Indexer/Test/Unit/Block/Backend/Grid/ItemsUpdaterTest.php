<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend\Grid;

class ItemsUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param bool $argument
     * @dataProvider updateDataProvider
     */
    public function testUpdate($argument)
    {
        $params = ['change_mode_onthefly' => 1, 'change_mode_changelog' => 2];

        $auth = $this->getMockBuilder('Magento\Framework\AuthorizationInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $auth->expects($this->once())->method('isAllowed')->with('Magento_Indexer::changeMode')->will($this->returnValue($argument));

        $model = new \Magento\Indexer\Block\Backend\Grid\ItemsUpdater($auth);
        $params = $model->update($params);
        $this->assertEquals($argument, (isset($params['change_mode_onthefly']) && isset($params['change_mode_changelog'])));
    }

    /**
     * @return array
     */
    public function updateDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
