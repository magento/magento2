<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\EntityManager\Test\Unit;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\TypeResolver;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TypeResolverTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var TypeResolver
     */
    private $resolver;

    /**
     * @var MetadataPool|MockObject
     */
    private $metadataPoolMock;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
        $this->metadataPoolMock =
            $this->createMock(MetadataPool::class);
        $this->resolver = new TypeResolver($this->metadataPoolMock);
    }

    /**
     * @param object $dataObject
     * @param string $interfaceName
     * @dataProvider resolveDataProvider
     */
    public function testResolve($dataObject, $interfaceName)
    {
        $customerDataObject = $this->objectManager->getObject($dataObject);
        $this->metadataPoolMock->expects($this->any())
            ->method('hasConfiguration')
            ->willReturnMap(
                [
                    [$interfaceName, true]
                ]
            );
        $this->assertEquals($interfaceName, $this->resolver->resolve($customerDataObject));
    }

    /**
     * @return array
     */
    public static function resolveDataProvider()
    {
        return [
            [
                Customer::class,
                CustomerInterface::class
            ],
            [
                Category::class,
                CategoryInterface::class,
            ]
        ];
    }
}
