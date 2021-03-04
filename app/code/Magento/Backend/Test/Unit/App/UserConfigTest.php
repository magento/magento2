<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Unit\App;

class UserConfigTest extends \PHPUnit\Framework\TestCase
{
    public function testUserRequestCreation()
    {
        $factoryMock = $this->createPartialMock(\Magento\Config\Model\Config\Factory::class, ['create']);
        $responseMock = $this->createMock(\Magento\Framework\App\Console\Response::class);
        $configMock = $this->createMock(\Magento\Config\Model\Config::class);

        $key = 'key';
        $value = 'value';
        $request = [$key => $value];
        $model = new \Magento\Backend\App\UserConfig($factoryMock, $responseMock, $request);
        $factoryMock->expects($this->once())->method('create')->willReturn($configMock);
        $configMock->expects($this->once())->method('setDataByPath')->with($key, $value);
        $configMock->expects($this->once())->method('save');

        $model->launch();
    }
}
