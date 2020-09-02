<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ui\Component\Form\Element;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test MultiSelect component.
 */
class MultiSelectTest extends TestCase
{
    /**
     * @var MultiSelectFactory
     */
    private $factory;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->factory = Bootstrap::getObjectManager()->get(MultiSelectFactory::class);
    }

    /**
     * Options data to verify
     *
     * @return array
     */
    public function getTestOptions(): array
    {
        return [
            'List' => [
                [
                    ['value' => '${\'my-value\'}', 'label' => 'My label'],
                    ['value' => '1', 'label' => 'Label'],
                    ['value' => '${\'my-value-2\'}', 'label' => 'This is ${\'My label\'}']
                ],
                [
                    ['value' => '${\'my-value\'}', 'label' => 'My label', '__disableTmpl' => ['value' => true]],
                    ['value' => '1', 'label' => 'Label'],
                    [
                        'value' => '${\'my-value-2\'}',
                        'label' => 'This is ${\'My label\'}',
                        '__disableTmpl' => ['value' => true, 'label' => true]
                    ]
                ]
            ],
            'provider' => [
                new class implements OptionSourceInterface
                {
                    /**
                     * @inheritDoc
                     */
                    public function toOptionArray()
                    {
                        return [['value' => '${\'value\'}', 'label' => 'Test']];
                    }
                },
                [['value' => '${\'value\'}', 'label' => 'Test', '__disableTmpl' => ['value' => true]]]
            ]
        ];
    }

    /**
     * Check that options received from an options provider properly initiated.
     *
     * @param array|OptionSourceInterface $options Options provided
     * @param array $expected Expected initialized options
     * @return void
     * @dataProvider getTestOptions
     */
    public function testOptions($options, array $expected): void
    {
        /** @var MultiSelect $component */
        $component = $this->factory->create(['options' => $options]);
        $component->prepare();

        $this->assertEquals($expected, $component->getData('config')['options']);
    }
}
