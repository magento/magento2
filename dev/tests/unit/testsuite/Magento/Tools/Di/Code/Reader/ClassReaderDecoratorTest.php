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
namespace Magento\Tools\Di\Code\Reader;

use Magento\Tools\Di\Compiler\ConstructorArgument;

class ClassReaderDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Di\Code\Reader\ClassReaderDecorator
     */
    private $model;

    /**
     * @var \Magento\Framework\Code\Reader\ClassReader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classReaderMock;

    protected function setUp()
    {
        $this->classReaderMock = $this->getMockBuilder('\Magento\Framework\Code\Reader\ClassReader')
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->model = new \Magento\Tools\Di\Code\Reader\ClassReaderDecorator($this->classReaderMock);
    }

    /**
     * @param $expectation
     * @param $className
     * @param $willReturn
     * @dataProvider getConstructorDataProvider
     */
    public function testGetConstructor($expectation, $className, $willReturn)
    {
        $this->classReaderMock->expects($this->once())
            ->method('getConstructor')
            ->with($className)
            ->willReturn($willReturn);
        $this->assertEquals(
            $expectation,
            $this->model->getConstructor($className)
        );
    }

    public function getConstructorDataProvider()
    {
        return [
            [null, 'null', null],
            [
                [new ConstructorArgument(['name', 'type', 'isRequired', 'defaultValue'])],
                'array',
                [['name', 'type', 'isRequired', 'defaultValue']]
            ]
        ];
    }

    public function testGetParents()
    {
        $stringArray = ['Parent_Class_Name1', 'Interface_1'];
        $this->classReaderMock->expects($this->once())
            ->method('getParents')
            ->with('Child_Class_Name')
            ->willReturn($stringArray);
        $this->assertEquals($stringArray, $this->model->getParents('Child_Class_Name'));
    }
}
