<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\EavGraphQl\Test\Unit\Model\Output\Value\Options;

use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\EavGraphQl\Model\Output\Value\Options\GetCustomSelectedOptionAttributes;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for GetCustomSelectedOptionAttributes
 *
 * @see GetCustomSelectedOptionAttributes
 */
class GetCustomSelectedOptionAttributesTest extends TestCase
{
    /**
     * @var GetCustomSelectedOptionAttributes
     */
    private GetCustomSelectedOptionAttributes $getSelectedOptions;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $options = [];
        foreach (['' => ' ', '12' => 'Red', '13' => 'White'] as $value => $label) {
            $option = $this->createMock(AttributeOptionInterface::class);
            $option->method('getValue')->willReturn((string) $value);
            $option->method('getLabel')->willReturn($label);
            $options[] = $option;
        }

        /** @var AbstractAttribute|MockObject $attribute */
        $attribute = $this->createMock(AbstractAttribute::class);
        $attribute->method('getOptions')->willReturn($options);

        $attributeRepository = $this->createMock(AttributeRepository::class);
        $attributeRepository->method('get')->with('catalog_product', 'color')->willReturn($attribute);

        $this->getSelectedOptions = new GetCustomSelectedOptionAttributes($attributeRepository);
    }

    /**
     * Selected options are returned in the order of the attribute options
     *
     * @return void
     */
    public function testSelectedOptionsFollowTheAttributeOptionsOrder(): void
    {
        $this->assertSame(
            [
                ['value' => '12', 'label' => 'Red'],
                ['value' => '13', 'label' => 'White']
            ],
            $this->getSelectedOptions->execute('catalog_product', 'color', '13,12')
        );
    }

    /**
     * An empty value does not select the empty option of the source model
     *
     * @return void
     */
    public function testEmptyValueSelectsNoOption(): void
    {
        $this->assertSame([], $this->getSelectedOptions->execute('catalog_product', 'color', ''));
    }
}
