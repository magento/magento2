<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Config\Source\Storage\Media;

use Magento\Framework\App\DeploymentConfig\ResourceConfig;

/**
 * Class DatabaseTest
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Backend\Model\Config\Source\Storage\Media\Database
     */
    protected $mediaDatabase;

    /**
     * @var \Magento\Framework\App\DeploymentConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    protected function setUp()
    {
        $this->configMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $this->configMock->expects(
            $this->any()
        )->method(
            'getSegment'
        )->with(
            ResourceConfig::CONFIG_KEY
        )->will(
            $this->returnValue(
            ['default_setup' => ['name' => 'default_setup', ResourceConfig::KEY_CONNECTION => 'connect1'],
                'custom_resource' => ['name' => 'custom_resource', ResourceConfig::KEY_CONNECTION => 'connect2'],
            ]
        )
        );
        $this->mediaDatabase = new \Magento\Backend\Model\Config\Source\Storage\Media\Database($this->configMock);
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
