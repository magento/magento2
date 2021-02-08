<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Test\Unit\Model\Di;

use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\ObjectManager\DefinitionInterface;
use Magento\Developer\Model\Di\PluginList;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Developer\Model\Di\Information;

class InformationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Developer\Model\Di\Information
     */
    private $object;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Magento\Framework\ObjectManager\ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var \Magento\Framework\ObjectManager\DefinitionInterface
     */
    private $definitions;

    /**
     * @var \Magento\Developer\Model\Di\PluginList
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
