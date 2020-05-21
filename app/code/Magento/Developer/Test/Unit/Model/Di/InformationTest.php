<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Di;

use Magento\Developer\Model\Di\Information;
use Magento\Developer\Model\Di\PluginList;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InformationTest extends TestCase
{
    /**
     * @var Information
     */
    private $object;

    /**
     * @var MockObject|ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var DefinitionInterface
     */
    private $definitions;

    /**
     * @var PluginList
     */
    private $pluginList;

    protected function setUp(): void
    {
        $this->objectManagerConfig = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->definitions = $this->getMockBuilder(DefinitionInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->pluginList = $this->getMockBuilder(PluginList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->object = (new ObjectManager($this))->getObject(Information::class, [
            'objectManagerConfig' => $this->objectManagerConfig,
            'definitions' => $this->definitions,
            'pluginList' => $this->pluginList,
        ]);
    }

    public function testGetPreference()
    {
        $this->objectManagerConfig->expects($this->any())
            ->method('getPreference')
            ->with(Information::class)
            ->willReturn(Information::class);
        $this->assertEquals(Information::class, $this->object->getPreference(Information::class));
    }

    public function testGetParameters()
    {
        $this->definitions->expects($this->any())
            ->method('getParameters')
            ->with(Information::class)
            ->willReturn([['information', Information::class, false, null]]);
        $this->objectManagerConfig->expects($this->any())
            ->method('getPreference')
            ->with(Information::class)
            ->willReturn(Information::class);
        $this->assertEquals(
            [['information', Information::class, null]],
            $this->object->getParameters(Information::class)
        );
    }
}
