<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Unit\Model\Search\RequestGenerator;


use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorCollection;
use Magento\CatalogSearch\Model\Search\RequestGenerator\GeneratorInterface;

class GeneratorCollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  GeneratorCollection */
    private $collection;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $defaultGenerator;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $datetimeGenerator;

    /** @var  GeneratorInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $rangeGenerator;

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
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->collection = $objectManager->getObject(
            GeneratorCollection::class,
            [
                'defaultGenerator' => $this->defaultGenerator,
                'generators' => [
                    'datetime' => $this->datetimeGenerator,
                    'range' => $this->datetimeGenerator,
                ],
            ]
        );
    }

    public function testGetSpecificGenerator()
    {
        $this->assertEquals($this->rangeGenerator, $this->collection->getGeneratorForType('range'));
        $this->assertEquals($this->datetimeGenerator, $this->collection->getGeneratorForType('datetime'));
    }

    public function testGetFallbackGenerator()
    {
        $this->assertEquals($this->defaultGenerator, $this->collection->getGeneratorForType('unknown_type'));
    }
}
