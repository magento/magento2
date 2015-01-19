<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class EncryptConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new EncryptConfig(['key' => 'testKey']);
        $this->assertNotEmpty($object->getKey());
    }

    public function testGetData()
    {
        $object = new EncryptConfig(['key' => 'testKey']);
        $this->assertSame(['key' => 'testKey'], $object->getData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No encryption key provided
     **/
    public function testEmptyData()
    {
        new EncryptConfig([]);
    }
}
