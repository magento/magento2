<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\MediaStorage\Test\Unit\Model\Config\Source\Storage\Media;

use Magento\Framework\App\DeploymentConfig;
use Magento\MediaStorage\Model\Config\Source\Storage\Media\Database;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @var Database
     */
    protected $mediaDatabase;

    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    protected function setUp(): void
    {
        $this->deploymentConfig = $this->createMock(DeploymentConfig::class);
        $this->deploymentConfig->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'resource'
        )->willReturn(
            [
                'default_setup' => ['name' => 'default_setup', 'connection' => 'connect1'],
                'custom_resource' => ['name' => 'custom_resource', 'connection' => 'connect2'],
            ]
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
