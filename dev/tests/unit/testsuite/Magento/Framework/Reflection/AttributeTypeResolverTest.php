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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\Reflection;


class AttributeTypeResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AttributeTypeResolver
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $typeProcessor;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reader;

    /**
     * Set up helper.
     */
    protected function setUp()
    {
        $this->typeProcessor = $this->getMock('\Magento\Framework\Reflection\TypeProcessor', [], [], '', false);
        $this->reader = $this->getMock('\Magento\Framework\Api\Config\Reader', [], [], '', false);
        $this->model = new AttributeTypeResolver($this->typeProcessor, $this->reader);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Provided value is not object type
     */
    public function testResolveObjectTypeWithNonObjectValue()
    {
        $code = 'some_code';
        $value = 'string';
        $context = 'Some\Class';
        $this->model->resolveObjectType($code, $value, $context);
    }

    public function testResolveObjectTypeWithoutConfiguredAttribute()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = 'Some\Class';

        $this->reader->expects($this->once())->method('read')->willReturn([]);
        $this->assertEquals('stdClass', $this->model->resolveObjectType($code, $value, $context));
    }

    public function testResolveObjectTypeWithConfiguredAttribute()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = '\Some\Class';
        $config = ['Some\Class' => ['some_code' => '\Magento\Framework\Object']];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with('\Magento\Framework\Object')
            ->willReturn('\Magento\Framework\Object');

        $this->reader->expects($this->once())->method('read')->willReturn($config);
        $this->assertEquals('\Magento\Framework\Object', $this->model->resolveObjectType($code, $value, $context));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Class "\Some\Class" does not exist. Please note that namespace must be specified.
     */
    public function testResolveObjectTypeWithConfiguredAttributeAndNonExistedClass()
    {
        $code = 'some_code';
        $value = new \stdClass();
        $context = '\Some\Class';
        $config = ['Some\Class' => ['some_code' => '\Some\Class']];

        $this->typeProcessor->expects($this->once())
            ->method('getArrayItemType')
            ->with('\Some\Class')
            ->willReturn('\Some\Class');

        $this->reader->expects($this->once())->method('read')->willReturn($config);
        $this->model->resolveObjectType($code, $value, $context);
    }
}
