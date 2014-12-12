<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
