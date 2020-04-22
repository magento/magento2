<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\App;

use Magento\Backend\App\UserConfig;
use Magento\Config\Model\Config;
use Magento\Config\Model\Config\Factory;
use Magento\Framework\App\Console\Response;
use PHPUnit\Framework\TestCase;

class UserConfigTest extends TestCase
{
    public function testUserRequestCreation()
    {
        $factoryMock = $this->createPartialMock(Factory::class, ['create']);
        $responseMock = $this->createMock(Response::class);
        $configMock = $this->createMock(Config::class);

        $key = 'key';
        $value = 'value';
        $request = [$key => $value];
        $model = new UserConfig($factoryMock, $responseMock, $request);
        $factoryMock->expects($this->once())->method('create')->willReturn($configMock);
        $configMock->expects($this->once())->method('setDataByPath')->with($key, $value);
        $configMock->expects($this->once())->method('save');

        $model->launch();
    }
}
