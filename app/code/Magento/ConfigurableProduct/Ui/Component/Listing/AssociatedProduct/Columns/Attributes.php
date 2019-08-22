<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface as AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Attributes extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Column name
     */
    const NAME = 'column.attributes';

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $attributes = $this->getAttributes();
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $attrStrings = [];
                foreach ($attributes as $attributeCode => $attribute) {
                    if (isset($item[$attributeCode]) && isset($attribute['options'][$item[$attributeCode]])) {
                        $attrStrings[] = $attribute['label'] . ': ' . $attribute['options'][$item[$attributeCode]];
                    }

                    $item[$fieldName] = implode(', ', $attrStrings);
                }
            }
        }

        return $dataSource;
    }

    /**
     * Get array of attributes information
     *
     * Array contains attribute label and options labels
     *
     * @return array
     */
    private function getAttributes()
    {
        $attributes = [];
        foreach ($this->attributeRepository->getList($this->getSearchCriteria())->getItems() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            $attributes[$attributeCode] = [
                'label' => $attribute->getDefaultFrontendLabel(),
            ];

            $options = $attribute->getOptions();
            if (is_array($options)) {
                foreach ($options as $option) {
                    $attributes[$attributeCode]['options'][$option->getValue()] = $option->getLabel();
                }
            }
        }

        return $attributes;
    }

    /**
     * Get SearchCriteria for attributeRepository
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function getSearchCriteria()
    {
        $attributesCodes = (array) $this->context->getRequestParam('attributes_codes', []);

        return $this->searchCriteriaBuilder
            ->addFilter('additional_table.is_used_in_grid', 1)
            ->addFilter('main_table.attribute_code', $attributesCodes, 'in')
            ->create();
    }
}
