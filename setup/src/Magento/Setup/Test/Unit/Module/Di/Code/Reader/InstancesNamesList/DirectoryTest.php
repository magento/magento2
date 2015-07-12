<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Module\Di\Code\Reader\InstancesNamesList;

use Magento\Setup\Module\Di\Compiler\Log\Log;

/**
 * Class DirectoryTest
 *
 * @package Magento\Setup\Module\Di\Code\Reader\Decorator
 */
class DirectoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\ClassesScanner | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classesScanner;

    /**
     * @var \Magento\Framework\Code\Reader\ClassReader | \PHPUnit_Framework_MockObject_MockObject
     */
    private $classReaderMock;

    /**
     * @var \Magento\Setup\Module\Di\Code\Reader\Decorator\Directory
     */
    private $model;

    /**
     * @var \Magento\Framework\Code\Validator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $validatorMock;

    /**
     * @var \Magento\Setup\Module\Di\Compiler\Log\Log | \PHPUnit_Framework_MockObject_MockObject
     */
    private $logMock;

    protected function setUp()
    {
        $this->logMock = $this->getMockBuilder('Magento\Setup\Module\Di\Compiler\Log\Log')
            ->disableOriginalConstructor()
            ->setMethods(['add'])
            ->getMock();

        $this->classesScanner = $this->getMockBuilder('\Magento\Setup\Module\Di\Code\Reader\ClassesScanner')
            ->disableOriginalConstructor()
            ->setMethods(['getList'])
            ->getMock();

        $this->classReaderMock = $this->getMockBuilder('\Magento\Framework\Code\Reader\ClassReader')
            ->disableOriginalConstructor()
            ->setMethods(['getParents'])
            ->getMock();

        $this->validatorMock = $this->getMockBuilder('\Magento\Framework\Code\Validator')
            ->disableOriginalConstructor()
            ->setMethods(['validate'])
            ->getMock();

        $this->model = new \Magento\Setup\Module\Di\Code\Reader\Decorator\Directory(
            $this->logMock,
            $this->classReaderMock,
            $this->classesScanner,
            $this->validatorMock,
            '/var/generation'
        );
    }

    public function testGetList()
    {
        $path = '/tmp/test';

        $classes = ['NameSpace1\ClassName1', 'NameSpace1\ClassName2'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $parents = [
            ['NameSpace1\ClassName1', ['Parent_Class_Name', 'Interface_1', 'Interface_2']],
            ['NameSpace1\ClassName2', ['Parent_Class_Name', 'Interface_1', 'Interface_2']]
        ];

        $this->classReaderMock->expects($this->exactly(count($classes)))
            ->method('getParents')
            ->will($this->returnValueMap(
                $parents
            ));

        $this->logMock->expects($this->never())
            ->method('add');

        $this->validatorMock->expects($this->exactly(count($classes)))
            ->method('validate');

        $this->model->getList($path);
        $result = $this->model->getRelations();

        $expected = [
            $classes[0] => $parents[0][1],
            $classes[1] => $parents[1][1]
        ];

        $this->assertEquals($result, $expected);
    }

    public function testGetListNoValidation()
    {
        $path = '/var/generation';

        $classes = ['NameSpace1\ClassName1', 'NameSpace1\ClassName2'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $parents = [
            ['NameSpace1\ClassName1', ['Parent_Class_Name', 'Interface_1', 'Interface_2']],
            ['NameSpace1\ClassName2', ['Parent_Class_Name', 'Interface_1', 'Interface_2']]
        ];

        $this->classReaderMock->expects($this->exactly(count($classes)))
            ->method('getParents')
            ->will($this->returnValueMap(
                $parents
            ));

        $this->logMock->expects($this->never())
            ->method('add');

        $this->validatorMock->expects($this->never())
            ->method('validate');

        $this->model->getList($path);
        $result = $this->model->getRelations();

        $expected = [
            $classes[0] => $parents[0][1],
            $classes[1] => $parents[1][1]
        ];

        $this->assertEquals($result, $expected);
    }

    /**
     * @dataProvider getListExceptionDataProvider
     *
     * @param $exception
     */
    public function testGetListException(\Exception $exception)
    {
        $path = '/tmp/test';

        $classes = ['NameSpace1\ClassName1'];

        $this->classesScanner->expects($this->once())
            ->method('getList')
            ->with($path)
            ->willReturn($classes);

        $this->logMock->expects($this->once())
            ->method('add')
            ->with(Log::COMPILATION_ERROR, $classes[0], $exception->getMessage());

        $this->validatorMock->expects($this->exactly(count($classes)))
            ->method('validate')
            ->will(
                $this->throwException($exception)
            );

        $this->model->getList($path);

        $result = $this->model->getRelations();

        $this->assertEquals($result, []);
    }

    /**
     * DataProvider for test testGetListException
     *
     * @return array
     */
    public function getListExceptionDataProvider()
    {
        return [
            [new \Magento\Framework\Exception\ValidatorException(new \Magento\Framework\Phrase('Not Valid!'))],
            [new \ReflectionException('Not Valid!')]
        ];
    }
}
