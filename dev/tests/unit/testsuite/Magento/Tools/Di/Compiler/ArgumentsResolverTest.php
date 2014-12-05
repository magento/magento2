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
namespace Magento\Tools\Di\Compiler;

class ArgumentsResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Compiler\ArgumentsResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $diContainerConfig;

    protected function setUp()
    {
        $this->diContainerConfig = $this->getMock('Magento\Framework\ObjectManager\ConfigInterface', [], [], '', false);
        $this->model = new \Magento\Tools\Di\Compiler\ArgumentsResolver($this->diContainerConfig);
    }

    /**
     * @dataProvider getResolvedConstructorArgumentsNoTypeDataProvider
     */
    public function testGetResolvedConstructorArgumentsNoType($constructor, $isRequired, $getType, $isShared, $expected)
    {
        $instanceType = ['instance' => 'Magento\Framework\Api\Config\MetadataConfig', 'argument' => 'object'];
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->willReturn(['virtualType' => $instanceType]);
        $constructor->expects($this->any())
            ->method('isRequired')
            ->willReturn($isRequired);
        $constructor->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder');
        $constructor->expects($this->any())
            ->method('getType')
            ->willReturn($getType);
        $constructor->expects($this->any())
            ->method('getName')
            ->willReturn('attributeMetadataBuilder');
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturn($isShared);

        $this->assertEquals(
            $expected,
            $this->model->getResolvedConstructorArguments($instanceType, [$constructor])
        );
    }

    /**
     * @return array
     */
    public function getResolvedConstructorArgumentsNoTypeDataProvider()
    {
        $constructor = $this->getMock('Magento\Tools\Di\Compiler\ConstructorArgument', [], [], '', false);
        $expected = [
            'attributeMetadataBuilder' => ['__val__' => 'Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder']
        ];
        return [
            [$constructor, false, false, true, $expected]
        ];
    }

    /**
     * @dataProvider getResolvedConstructorArgumentsWithTypeDataProvider
     */
    public function testGetResolvedConstructorArgumentsWithType(
        $constructor, $isRequired, $getType, $isShared, $expected
    ) {
        $instanceType = ['instance' => 'Magento\Framework\Api\Config\MetadataConfig'];
        if (!$constructor) {
            $this->assertNull($this->model->getResolvedConstructorArguments('virtualType', $constructor));
            return;
        }

        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->willReturn(['virtualType' => $instanceType]);
        $constructor->expects($this->any())
            ->method('isRequired')
            ->willReturn($isRequired);
        $constructor->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder');
        $constructor->expects($this->any())
            ->method('getType')
            ->willReturn($getType);
        $constructor->expects($this->any())
            ->method('getName')
            ->willReturn('virtualType');
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturn($isShared);

        $this->assertEquals(
            $expected,
            $this->model->getResolvedConstructorArguments($instanceType, [$constructor])
        );
    }

    /**
     * @return array
     */
    public function getResolvedConstructorArgumentsWithTypeDataProvider()
    {
        $constructor = $this->getMock('Magento\Tools\Di\Compiler\ConstructorArgument', [], [], '', false);
        return [
            [$constructor, true, 'virtualType', true, ['virtualType' => 'Magento\Framework\Api\Config\MetadataConfig']],
        ];
    }

    public function testGetResolvedConstructorArgumentsConstructorNull()
    {
        $this->assertNull($this->model->getResolvedConstructorArguments('virtualType', []));
    }

    /**
     * @dataProvider getResolvedConstructorArgumentsWithArgumentDataProvider
     */
    public function testGetResolvedConstructorArgumentsWithArgument(
        $constructor, $isRequired, $getType, $isShared, $expected
    ) {
        $instanceType = ['instance' => 'Magento\Framework\Api\Config\MetadataConfig', 'argument' => 'object'];
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->willReturn(['virtualType' => $instanceType]);
        $constructor->expects($this->any())
            ->method('isRequired')
            ->willReturn($isRequired);
        $constructor->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder');
        $constructor->expects($this->any())
            ->method('getType')
            ->willReturn($getType);
        $constructor->expects($this->any())
            ->method('getName')
            ->willReturn('virtualType');
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturn($isShared);

        $this->assertEquals(
            $expected,
            $this->model->getResolvedConstructorArguments($instanceType, [$constructor])
        );
    }

    /**
     * @return array
     */
    public function getResolvedConstructorArgumentsWithArgumentDataProvider()
    {
        $constructor = $this->getMock('Magento\Tools\Di\Compiler\ConstructorArgument', [], [], '', false);
        $expected = [
            'virtualType' => [
                '__arg__' => 'object',
                '__default__' => 'Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder'
            ]
        ];
        return [
            [$constructor, false, false, true, $expected]
        ];
    }

    /**
     * @dataProvider getResolvedConstructorArgumentsNoSharedDataProvider
     */
    public function testGetResolvedConstructorArgumentsNoShared(
        $constructor, $isRequired, $getType, $isShared, $expected
    ) {
        $instanceType = ['instance' => 'Magento\Framework\Api\Config\MetadataConfig', 'argument' => 'object'];
        $this->diContainerConfig->expects($this->any())
            ->method('getArguments')
            ->willReturn(['virtualType' => $instanceType]);
        $constructor->expects($this->any())
            ->method('isRequired')
            ->willReturn($isRequired);
        $constructor->expects($this->any())
            ->method('getDefaultValue')
            ->willReturn('Magento\Customer\Service\V1\Data\Eav\AttributeMetadataBuilder');
        $constructor->expects($this->any())
            ->method('getType')
            ->willReturn($getType);
        $constructor->expects($this->any())
            ->method('getName')
            ->willReturn('virtualType');
        $this->diContainerConfig->expects($this->any())
            ->method('isShared')
            ->willReturn($isShared);

        $this->assertEquals(
            $expected,
            $this->model->getResolvedConstructorArguments($instanceType, [$constructor])
        );
    }

    /**
     * @return array
     */
    public function getResolvedConstructorArgumentsNoSharedDataProvider()
    {
        $constructor = $this->getMock('Magento\Tools\Di\Compiler\ConstructorArgument', [], [], '', false);
        $expected = [
            'virtualType' => [
                '__non_shared__' => true,
                '__instance__' => 'Magento\Framework\Api\Config\MetadataConfig'
            ]
        ];
        return [
            [$constructor, false, true, false, $expected]
        ];
    }
}
