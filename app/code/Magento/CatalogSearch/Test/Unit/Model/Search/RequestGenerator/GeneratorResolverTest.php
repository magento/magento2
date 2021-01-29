<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;

use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorResolver;

class GeneratorResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var  GeneratorResolver */
    private $resolver;

    /** @var  GeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $defaultGenerator;

    /** @var  GeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $datetimeGenerator;

    /** @var  GeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $rangeGenerator;

    protected function setUp(): void
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

    public function testGetSpecificGenerator()
    {
        $this->assertEquals($this->rangeGenerator, $this->resolver->getGeneratorForType('range'));
        $this->assertEquals($this->datetimeGenerator, $this->resolver->getGeneratorForType('datetime'));
    }

    public function testGetFallbackGenerator()
    {
        $this->assertEquals($this->defaultGenerator, $this->resolver->getGeneratorForType('unknown_type'));
    }

    /**
     */
    public function testGetInvalidGeneratorType()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->resolver->getGeneratorForType('invalid_type');
    }
}
