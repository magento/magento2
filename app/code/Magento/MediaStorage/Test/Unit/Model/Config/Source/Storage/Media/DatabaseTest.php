<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\MediaStorage\Test\Unit\Model\Config\Source\Storage\Media;

use Magento\Setup\Model\ConfigOptionsList;

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
     * @var \Magento\Framework\App\DeploymentConfig\Reader|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $readerMock;

    protected function setUp()
    {
        $this->readerMock = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $this->readerMock->expects(
            $this->any()
        )->method(
            'getConfigData'
        )->with(
                ConfigOptionsList::KEY_RESOURCE
        )->will(
            $this->returnValue(
            [
                'default_setup' => ['name' => 'default_setup', ConfigOptionsList::KEY_CONNECTION => 'connect1'],
                'custom_resource' => ['name' => 'custom_resource', ConfigOptionsList::KEY_CONNECTION => 'connect2'],
            ]
        )
        );
        $this->mediaDatabase = new \Magento\MediaStorage\Model\Config\Source\Storage\Media\Database($this->readerMock);
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
