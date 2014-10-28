<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filesystem\File;

use Magento\Framework\Filesystem\DriverPool;

/**
 * Class ReadFactoryTest
 */
class ReadFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string|null $protocol
     * @param \PHPUnit_Framework_MockObject_MockObject|null $driver
     * @dataProvider createProvider
     */
    public function testCreate($protocol, $driver)
    {
        $driverPool = $this->getMock('Magento\Framework\Filesystem\DriverPool', ['getDriver']);
        if ($protocol) {
            $driverMock = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
            $driverMock->expects($this->any())->method('isExists')->willReturn(true);
            $driverPool->expects($this->once())->method('getDriver')->willReturn($driverMock);
        } else {
            $driverPool->expects($this->never())->method('getDriver');
        }
        $factory = new ReadFactory($driverPool);
        $result = $factory->create('path', $protocol, $driver);
        $this->assertInstanceOf('Magento\Framework\Filesystem\File\Read', $result);
    }

    /**
     * @return array
     */
    public function createProvider()
    {
        $driver = $this->getMockForAbstractClass('Magento\Framework\Filesystem\DriverInterface');
        $driver->expects($this->any())->method('isExists')->willReturn(true);
        return [
            [null, $driver],
            ['custom_protocol', null]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateException()
    {
        $factory = new ReadFactory(new DriverPool);
        $factory->create('path');
    }
}
