<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
