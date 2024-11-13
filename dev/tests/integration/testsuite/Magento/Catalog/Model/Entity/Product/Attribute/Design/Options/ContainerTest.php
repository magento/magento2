<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Entity\Product\Attribute\Design\Options;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks that product design options container return correct options.
 *
 * @see \Magento\Catalog\Model\Entity\Product\Attribute\Design\Options\Container
 */
class ContainerTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Container */
    private $container;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->container = $this->objectManager->get(Container::class);
    }

    /**
     * @dataProvider getOptionTextDataProvider
     * @param string $value
     * @param string|bool $expectedValue
     * @return void
     */
    public function testGetOptionText(string $value, $expectedValue): void
    {
        $actualValue = $this->container->getOptionText($value);
        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @return array
     */
    public static function getOptionTextDataProvider(): array
    {
        return [
            'with_value' => [
                'value' => 'container2',
                'expectedValue' => __('Block after Info Column'),
            ],
            'with_not_valid_value' => [
                'value' => 'container3',
                'expectedValue' => false,
            ],
        ];
    }
}
