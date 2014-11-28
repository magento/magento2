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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App;

class DeploymentConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private static $fixture = [
        'segment1' => 'scalar_value',
        'segment2' => [
            'foo' => 1,
            'bar' => ['baz' => 2],
        ]
    ];

    /**
     * @var array
     */
    private static $flattenedFixture = [
        'segment1' => 'scalar_value',
        'segment2/foo' => 1,
        'segment2/bar/baz' => 2,
    ];

    /**
     * @var array
     */
    protected static $fixtureConfig;

    /**
     * @var array
     */
    protected static $fixtureConfigMerged;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfigMerged;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $reader;

    public static function setUpBeforeClass()
    {
        self::$fixtureConfig = require __DIR__ . '/_files/config.php';
        self::$fixtureConfigMerged = require __DIR__ . '/_files/other/local_developer_merged.php';
    }

    protected function setUp()
    {
        $this->reader = $this->getMock('Magento\Framework\App\DeploymentConfig\Reader', [], [], '', false);
        $this->_deploymentConfig = new \Magento\Framework\App\DeploymentConfig($this->reader, array());
        $this->_deploymentConfigMerged = new \Magento\Framework\App\DeploymentConfig(
            $this->reader,
            require __DIR__ . '/_files/other/local_developer.php'
        );
    }

    public function testGetters()
    {
        $this->reader->expects($this->once())->method('load')->willReturn(self::$fixture);
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        // second time to ensure loader will be invoked only once
        $this->assertSame(self::$flattenedFixture, $this->_deploymentConfig->get());
        $this->assertSame('scalar_value', $this->_deploymentConfig->getSegment('segment1'));
        $this->assertSame(self::$fixture['segment2'], $this->_deploymentConfig->getSegment('segment2'));
    }

    public function testIsAvailable()
    {
        $this->reader->expects($this->once())->method('load')->willReturn(['a' => 1]);
        $object = new DeploymentConfig($this->reader);
        $this->assertTrue($object->isAvailable());
    }

    public function testNotAvailable()
    {
        $this->reader->expects($this->once())->method('load')->willReturn([]);
        $object = new DeploymentConfig($this->reader);
        $this->assertFalse($object->isAvailable());
    }

    /**
     * @param array $data
     * @expectedException \Exception
     * @expectedExceptionMessage Key collision
     * @dataProvider keyCollisionDataProvider
     */
    public function testKeyCollision(array $data)
    {
        $this->reader->expects($this->once())->method('load')->willReturn($data);
        $object = new DeploymentConfig($this->reader);
        $object->get();
    }

    public function keyCollisionDataProvider()
    {
        return [
            [
                ['foo' => ['bar' => '1'], 'foo/bar' => '2'],
                ['foo/bar' => '1', 'foo' => ['bar' => '2']],
                ['foo' => ['subfoo' => ['subbar' => '1'], 'subfoo/subbar' => '2'], 'bar' => '3'],
            ]
        ];
    }

}
