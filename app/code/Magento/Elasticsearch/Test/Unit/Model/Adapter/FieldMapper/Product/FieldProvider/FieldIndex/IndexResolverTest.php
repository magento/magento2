<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\ConverterInterface;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD)
 */
class IndexResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\IndexResolver
     */
    private $resolver;

    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * Set up test environment
     *
     * @return void
     */
    protected function setUp()
    {
        $this->converter = $this->getMockBuilder(ConverterInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMockForAbstractClass();
        $objectManager = new ObjectManagerHelper($this);

        $this->resolver = $objectManager->getObject(
            \Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldIndex\IndexResolver::class,
            [
                'converter' => $this->converter
            ]
        );
    }

    /**
     * @dataProvider getFieldIndexProvider
     * @param $isSearchable
     * @param $isAlwaysIndexable
     * @param $serviceFieldType
     * @param $expected
     * @return void
     */
    public function testGetFieldName(
        $isSearchable,
        $isAlwaysIndexable,
        $serviceFieldType,
        $expected
    ) {
        $this->converter->expects($this->any())
            ->method('convert')
            ->willReturn('something');
        $attributeMock = $this->getMockBuilder(AttributeAdapter::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'isSearchable',
                'isAlwaysIndexable',
            ])
            ->getMock();
        $attributeMock->expects($this->any())
            ->method('isSearchable')
            ->willReturn($isSearchable);
        $attributeMock->expects($this->any())
            ->method('isAlwaysIndexable')
            ->willReturn($isAlwaysIndexable);

        $this->assertEquals(
            $expected,
            $this->resolver->getFieldIndex($attributeMock, $serviceFieldType)
        );
    }

    /**
     * @return array
     */
    public function getFieldIndexProvider()
    {
        return [
            [true, true, 'string', null],
            [false, false, 'string', 'something'],
            [true, false, 'string', null],
            [false, true, 'string', null],
        ];
    }
}
