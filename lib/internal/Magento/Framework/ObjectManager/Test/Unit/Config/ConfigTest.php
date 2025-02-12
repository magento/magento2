<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Test\Unit\Config;

use Magento\Framework\ObjectManager\Config\Config;
use Magento\Framework\ObjectManager\ConfigCacheInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\ObjectManager\Helper\SortItems;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManagerHelper;

    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManager($this);
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
        $definitions = $this->getMockForAbstractClass(DefinitionInterface::class);
        $definitions->expects($this->once())->method('getClasses')->willReturn(['FooType']);

        $cache = $this->getMockForAbstractClass(ConfigCacheInterface::class);
        $cache->expects($this->once())->method('get')->willReturn(false);

        $serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $serializerMock->expects($this->atLeast(2))
            ->method('serialize')
            ->willReturn('[[],[],[],[]]');

        $sortItemsMock = $this->getMockForAbstractClass(SortItems::class);
        $config =$this->objectManagerHelper->getObject(
            Config::class,
            [
                'relations' => null,
                'definitions' => $definitions,
                'sortItemsHelper' => $sortItemsMock,
            ]
        );
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
