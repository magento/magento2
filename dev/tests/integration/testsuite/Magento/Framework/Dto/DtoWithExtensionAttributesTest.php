<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Dto\DtoProcessor\DtoReflection;
use Magento\Framework\Dto\Mock\ImmutableDto;
use Magento\Framework\Dto\Mock\ImmutableNestedDto;
use Magento\Framework\Dto\Mock\MockDtoConfig;
use Magento\Framework\Dto\Mock\TestSimpleObject;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DtoWithExtensionAttributesTest extends TestCase
{
    /* @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();


    }

    private function mockExtensionAttributes(): void
    {
        /** @var Config $config */
        $config = Bootstrap::getObjectManager()->get(Config::class);
        $config->merge([TestDataInterface::class => [
            'attribute1' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute2' => [
                'type' => 'string',
                'resourceRefs' => '',
                'join' => null
            ],
            'attribute3' => [
                'type' => TestDataObjectSub::class . '[]',
                'resourceRefs' => '',
                'join' => null
            ]
        ]]);
    }
}
