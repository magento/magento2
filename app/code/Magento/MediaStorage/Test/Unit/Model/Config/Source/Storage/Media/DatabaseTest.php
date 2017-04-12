<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\MediaStorage\Test\Unit\Model\Config\Source\Storage\Media;

use Magento\MediaStorage\Model\Config\Source\Storage\Media\Database;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\MediaStorage\Model\Config\Source\Storage\Media\Database
     */
    protected $mediaDatabase;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $deploymentConfig;

    protected function setUp()
    {
        $this->deploymentConfig = $this->getMock(\Magento\Framework\App\DeploymentConfig::class, [], [], '', false);
        $this->deploymentConfig->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'resource'
        )->will(
            $this->returnValue(
            [
                'default_setup' => ['name' => 'default_setup', 'connection' => 'connect1'],
                'custom_resource' => ['name' => 'custom_resource', 'connection' => 'connect2'],
            ]
        )
        );
        $this->mediaDatabase = new Database($this->deploymentConfig);
    }

    /**
     * test to option array
     */
    public function testToOptionArray()
    {
        $this->assertNotEquals(
            $this->mediaDatabase->toOptionArray(),
            [
                ['value' => 'default_setup', 'label' => 'default_setup'],
                ['value' => 'custom_resource', 'label' => 'custom_resource']
            ]
        );

        $this->assertEquals(
            $this->mediaDatabase->toOptionArray(),
            [
                ['value' => 'custom_resource', 'label' => 'custom_resource'],
                ['value' => 'default_setup', 'label' => 'default_setup']
            ]
        );
        $this->assertEquals(
            current($this->mediaDatabase->toOptionArray()),
            ['value' => 'custom_resource', 'label' => 'custom_resource']
        );
    }
}
