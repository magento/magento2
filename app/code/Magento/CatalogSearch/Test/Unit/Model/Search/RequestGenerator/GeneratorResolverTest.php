<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;

use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorInterface;

/**
 * Test for Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver.
 */
class GeneratorResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var  GeneratorResolver */
    private $resolver;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $defaultGenerator;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $datetimeGenerator;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $rangeGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->defaultGenerator = $this->getMockBuilder(GeneratorInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->datetimeGenerator = $this->getMockBuilder(GeneratorInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $this->rangeGenerator = $this->getMockBuilder(GeneratorInterface::class)
            ->setMethods([])
            ->getMockForAbstractClass();

        $invalidTypeGenerator = $this->getMockBuilder(\stdClass::class)
            ->setMethods([]);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->resolver = $objectManager->getObject(
            GeneratorResolver::class,
            [
                'defaultGenerator' => $this->defaultGenerator,
                'generators' => [
                    'datetime' => $this->datetimeGenerator,
                    'range' => $this->datetimeGenerator,
                    'invalid_type' => $invalidTypeGenerator,
                ],
            ]
        );
    }

    /**
     * Tests resolving type specific search generator.
     *
     * @return void
     */
    public function testGetSpecificGenerator()
    {
        $this->assertEquals($this->rangeGenerator, $this->resolver->getGeneratorForType('range'));
        $this->assertEquals($this->datetimeGenerator, $this->resolver->getGeneratorForType('datetime'));
    }

    /**
     * Tests resolving fallback search generator.
     *
     * @return void
     */
    public function testGetFallbackGenerator()
    {
        $this->assertEquals($this->defaultGenerator, $this->resolver->getGeneratorForType('unknown_type'));
    }

    /**
     * Tests resolving search generator with invalid type.
     *
     * @expectedException InvalidArgumentException
     * @return void
     */
    public function testGetInvalidGeneratorType()
    {
        $this->resolver->getGeneratorForType('invalid_type');
    }
}
