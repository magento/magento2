<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\Serialize\SerializerInterface;
use \Magento\Framework\ObjectManager\Config\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testGetArgumentsEmpty()
    {
        $config = new Config();
        $this->assertSame([], $config->getArguments('An invalid type'));
    }

    public function testExtendMergeConfiguration()
    {
        $this->_assertFooTypeArguments(new Config());
    }

    /**
     * A primitive fixture for testing merging arguments
     *
     * @param Config $config
     */
    private function _assertFooTypeArguments(Config $config)
    {
        $expected = ['argName' => 'argValue'];
        $fixture = ['FooType' => ['arguments' => $expected]];
        $config->extend($fixture);
        $this->assertEquals($expected, $config->getArguments('FooType'));
    }

    public function testExtendWithCacheMock()
    {
        $definitions = $this->getMock(\Magento\Framework\ObjectManager\DefinitionInterface::class);
        $definitions->expects($this->once())->method('getClasses')->will($this->returnValue(['FooType']));

        $cache = $this->getMock(\Magento\Framework\ObjectManager\ConfigCacheInterface::class);
        $cache->expects($this->once())->method('get')->will($this->returnValue(false));

        $config = new Config(null, $definitions);
        $serializerMock = $this->getMock(SerializerInterface::class);
        $serializerMock->expects($this->exactly(2))
            ->method('serialize');
        $this->objectManagerHelper->setBackwardCompatibleProperty(
            $config,
            'serializer',
            $serializerMock
        );
        $config->setCache($cache);

        $this->_assertFooTypeArguments($config);
    }

    public function testGetPreferenceTrimsFirstSlash()
    {
        $config = new Config();
        $this->assertEquals('Some\Class\Name', $config->getPreference('\Some\Class\Name'));
    }

    public function testExtendIgnoresFirstSlashesOnPreferences()
    {
        $config = new Config();
        $config->extend(['preferences' => ['\Some\Interface' => '\Some\Class']]);
        $this->assertEquals('Some\Class', $config->getPreference('Some\Interface'));
        $this->assertEquals('Some\Class', $config->getPreference('\Some\Interface'));
    }

    public function testExtendIgnoresFirstShashesOnVirtualTypes()
    {
        $config = new Config();
        $config->extend(['\SomeVirtualType' => ['type' => '\Some\Class']]);
        $this->assertEquals('Some\Class', $config->getInstanceType('SomeVirtualType'));
    }

    public function testExtendIgnoresFirstShashes()
    {
        $config = new Config();
        $config->extend(['\Some\Class' => ['arguments' => ['someArgument']]]);
        $this->assertEquals(['someArgument'], $config->getArguments('Some\Class'));
    }

    public function testExtendIgnoresFirstShashesForSharing()
    {
        $config = new Config();
        $config->extend(['\Some\Class' => ['shared' => true]]);
        $this->assertTrue($config->isShared('Some\Class'));
    }
}
