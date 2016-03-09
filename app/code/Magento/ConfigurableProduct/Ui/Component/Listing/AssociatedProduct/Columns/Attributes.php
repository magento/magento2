<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Catalog\Ui\Component\Listing\Attribute\RepositoryInterface as AttributeRepository;

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
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AttributeRepository $attributeRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AttributeRepository $attributeRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->attributeRepository = $attributeRepository;
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
                $attributeString = '';
                foreach ($attributes as $attributeCode => $attribute) {
                    if (isset($item[$attributeCode]) && isset($attribute['options'][$item[$attributeCode]])) {
                        if ($attributeString) {
                            $attributeString .= ', ';
                        }

                        $attributeString .= $attribute['label'] . ': ' . $attribute['options'][$item[$attributeCode]];
                    }

                    $item[$fieldName] = $attributeString;
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
        $attributesCodes = (array) $this->context->getRequestParam('attributes_codes', []);
        $attributes = [];
        foreach ($this->attributeRepository->getList() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!in_array($attributeCode, $attributesCodes)) {
                continue;
            }

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
}
