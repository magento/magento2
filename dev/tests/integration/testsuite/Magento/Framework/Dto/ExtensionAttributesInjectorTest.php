<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Dto;

use Magento\Framework\Dto\Mock\ConfigureTestDtos;
use Magento\Framework\Dto\Mock\ImmutableDtoWithInjectors;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test extension attributes injectors
 */
class ExtensionAttributesInjectorTest extends TestCase
{
    /**
     * @var DtoProcessor
     */
    private $dtoProcessor;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();

        ConfigureTestDtos::execute();
        $this->dtoProcessor = $objectManager->get(DtoProcessor::class);
    }

    public function testShouldInjectExtensionAttributesThroughInjectors(): void
    {
        /** @var ImmutableDtoWithInjectors $dto */
        $dto = $this->dtoProcessor->createFromArray(
            [
                'prop1' => 1
            ],
            ImmutableDtoWithInjectors::class
        );

        self::assertSame('value1', $dto->getExtensionAttributes()->getAttribute1());
    }
}
