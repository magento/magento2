<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\DeploymentConfig;

class SessionConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testGetKey()
    {
        $object = new SessionConfig([]);
        $this->assertNotEmpty($object->getKey());
    }

    /**
     * @param array $data
     * @param string $expected
     * @dataProvider getDataDataProvider
     */
    public function testGetData($data, $expected)
    {
        $object = new SessionConfig([$data]);
        $this->assertSame(['save' => $expected], $object->getData());
    }

    public function getDataDataProvider()
    {
        return [
            [[], 'files'],
            [['save' => 'files'], 'files'],
            [['save' => 'db'], 'files'],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid session_save location
     */
    public function testInvalidData()
    {
        new SessionConfig(['save' => 'invalid']);
    }
}
