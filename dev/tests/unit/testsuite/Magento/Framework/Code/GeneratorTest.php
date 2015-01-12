<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Code;

class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Class name parameter value
     */
    const SOURCE_CLASS = 'testClassName';

    /**
     * Expected generated entities
     *
     * @var array
     */
    protected $expectedEntities = [
        'factory' => \Magento\Framework\ObjectManager\Code\Generator\Factory::ENTITY_TYPE,
        'proxy' => \Magento\Framework\ObjectManager\Code\Generator\Proxy::ENTITY_TYPE,
        'interceptor' => \Magento\Framework\Interception\Code\Generator\Interceptor::ENTITY_TYPE,
    ];

    /**
     * Model under test
     *
     * @var \Magento\Framework\Code\Generator
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Generator\Io
     */
    protected $ioObjectMock;

    protected function setUp()
    {
        $this->ioObjectMock = $this->getMockBuilder('\Magento\Framework\Code\Generator\Io')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function tearDown()
    {
        unset($this->model);
    }

    public function testGetGeneratedEntities()
    {
        $this->model = new \Magento\Framework\Code\Generator(
            $this->ioObjectMock,
            ['factory', 'proxy', 'interceptor']
        );
        $this->assertEquals(array_values($this->expectedEntities), $this->model->getGeneratedEntities());
    }

    /**
     * @expectedException \Magento\Framework\Exception
     * @dataProvider generateValidClassDataProvider
     */
    public function testGenerateClass($className, $entityType)
    {
        $this->model = new \Magento\Framework\Code\Generator(
            $this->ioObjectMock,
            [
                'factory' => '\Magento\Framework\ObjectManager\Code\Generator\Factory',
                'proxy' => '\Magento\Framework\ObjectManager\Code\Generator\Proxy',
                'interceptor' => '\Magento\Framework\Interception\Code\Generator\Interceptor'
            ]
        );

        $this->model->generateClass($className . $entityType);
    }

    /**
     * @dataProvider generateValidClassDataProvider
     */
    public function testGenerateClassWithExistName($className, $entityType)
    {
        $definedClassesMock = $this->getMock('Magento\Framework\Code\Generator\DefinedClasses');
        $definedClassesMock->expects($this->any())
            ->method('classLoadable')
            ->willReturn(true);
        $this->model = new \Magento\Framework\Code\Generator(
            $this->ioObjectMock,
            [
                'factory' => '\Magento\Framework\ObjectManager\Code\Generator\Factory',
                'proxy' => '\Magento\Framework\ObjectManager\Code\Generator\Proxy',
                'interceptor' => '\Magento\Framework\Interception\Code\Generator\Interceptor'
            ],
            $definedClassesMock
        );

        $this->assertEquals(
            \Magento\Framework\Code\Generator::GENERATION_SKIP,
            $this->model->generateClass($className . $entityType)
        );
    }

    public function testGenerateClassWithWrongName()
    {
        $this->model = new \Magento\Framework\Code\Generator($this->ioObjectMock);

        $this->assertEquals(
            \Magento\Framework\Code\Generator::GENERATION_ERROR,
            $this->model->generateClass(self::SOURCE_CLASS)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception
     */
    public function testGenerateClassWithError()
    {
        $this->model = new \Magento\Framework\Code\Generator(
            $this->ioObjectMock,
            [
                'factory' => '\Magento\Framework\ObjectManager\Code\Generator\Factory',
                'proxy' => '\Magento\Framework\ObjectManager\Code\Generator\Proxy',
                'interceptor' => '\Magento\Framework\Interception\Code\Generator\Interceptor'
            ]
        );

        $expectedEntities = array_values($this->expectedEntities);
        $resultClassName = self::SOURCE_CLASS . ucfirst(array_shift($expectedEntities));

        $this->model->generateClass($resultClassName);
    }

    /**
     * Data provider for generate class tests
     *
     * @return array
     */
    public function generateValidClassDataProvider()
    {
        $data = [];
        foreach ($this->expectedEntities as $generatedEntity) {
            $generatedEntity = ucfirst($generatedEntity);
            $data['test class for ' . $generatedEntity] = [
                'class name' => self::SOURCE_CLASS,
                'entity type' => $generatedEntity,
            ];
        }
        return $data;
    }
}
