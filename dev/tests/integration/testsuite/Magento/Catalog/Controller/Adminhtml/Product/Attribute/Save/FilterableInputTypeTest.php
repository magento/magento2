<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute\Save;

use Magento\Store\Model\Store;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Verify admin save rejects layered navigation for unsupported catalog input types.
 *
 * @magentoDbIsolation enabled
 * @magentoAppArea adminhtml
 */
class FilterableInputTypeTest extends AbstractSaveAttributeTest
{
    /**
     * @param array $attributePostData
     * @param string $errorMessage
     * @return void
     */
    #[DataProvider('disallowedFilterableDataProvider')]
    public function testCreateAttributeWithLayeredNavigationIsRejected(
        array $attributePostData,
        string $errorMessage
    ): void {
        $this->createAttributeUsingDataWithErrorAndAssert($attributePostData, $errorMessage);
    }

    /**
     * @param array $attributePostData
     * @param array $checkArray
     * @return void
     */
    #[DataProvider('allowedFilterableDataProvider')]
    public function testCreateAttributeWithLayeredNavigationIsAllowed(
        array $attributePostData,
        array $checkArray
    ): void {
        $this->createAttributeUsingDataAndAssert($attributePostData, $checkArray);
    }

    /**
     * @return array
     */
    public static function disallowedFilterableDataProvider(): array
    {
        $message = (string)__(
            'Can be used only with catalog input type Yes/No, Dropdown, Multiple Select and Price.'
        );

        $cases = [];
        foreach (['text', 'textarea', 'texteditor', 'date', 'datetime', 'media_image'] as $frontendInput) {
            $cases[$frontendInput . '_is_filterable'] = [
                self::postData($frontendInput, 'filterable_' . $frontendInput, '1', '0'),
                $message,
            ];
        }

        $cases['text_is_filterable_in_search'] = [
            self::postData('text', 'filterable_text_search', '0', '1'),
            $message,
        ];

        return $cases;
    }

    /**
     * @return array
     */
    public static function allowedFilterableDataProvider(): array
    {
        $cases = [];
        foreach (['boolean', 'price'] as $frontendInput) {
            $attributeCode = 'allowed_filterable_' . $frontendInput;
            $cases[$frontendInput . '_is_filterable'] = [
                self::postData($frontendInput, $attributeCode, '1', '0'),
                [
                    'attribute_code' => $attributeCode,
                    'frontend_input' => $frontendInput,
                    'is_filterable' => 1,
                ],
            ];
        }

        return $cases;
    }

    /**
     * @param string $frontendInput
     * @param string $attributeCode
     * @param string $isFilterable
     * @param string $isFilterableInSearch
     * @return array
     */
    private static function postData(
        string $frontendInput,
        string $attributeCode,
        string $isFilterable,
        string $isFilterableInSearch
    ): array {
        return [
            'frontend_label' => [
                Store::DEFAULT_STORE_ID => 'Test attribute name',
            ],
            'frontend_input' => $frontendInput,
            'is_required' => '0',
            'attribute_code' => $attributeCode,
            'is_global' => '1',
            'is_unique' => '0',
            'is_searchable' => $isFilterableInSearch === '1' ? '1' : '0',
            'is_filterable' => $isFilterable,
            'is_filterable_in_search' => $isFilterableInSearch,
        ];
    }
}
