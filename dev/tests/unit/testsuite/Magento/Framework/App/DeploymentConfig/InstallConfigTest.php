<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\DeploymentConfig;

class InstallConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new InstallConfig(['date' => date('r')]);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $date = date('r');
        $object = new InstallConfig(['date' => $date]);
        $this->assertSame(['date' => $date], $object->getData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Install date not provided
     */
    public function testEmptyData()
    {
        new InstallConfig([]);
    }
}
