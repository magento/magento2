<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Backend\App;

class UserConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testUserRequestCreation()
    {
        $factoryMock = $this->getMock('Magento\Backend\Model\Config\Factory', [], [], '', false);
        $responseMock = $this->getMock('Magento\Framework\App\Console\Response', [], [], '', false);
        $configMock = $this->getMock('Magento\Backend\Model\Config', [], [], '', false);

        $key = 'key';
        $value = 'value';
        $request = [$key => $value];
        $model = new UserConfig($factoryMock, $responseMock, $request);
        $factoryMock->expects($this->once())->method('create')->will($this->returnValue($configMock));
        $configMock->expects($this->once())->method('setDataByPath')->with($key, $value);
        $configMock->expects($this->once())->method('save');

        $model->launch();
    }
}
