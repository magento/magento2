<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Reporting $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param AttributeRepository $attributeRepository
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Reporting $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        AttributeRepository $attributeRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->attributeRepository = $attributeRepository;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * Retrieve custom attributes options
     *
     * @return array
     */
    public function getCustomAttributesOptions()
    {
        $attributes = [];
        foreach ($this->attributeRepository->getList() as $attributeCode => $attributeData) {
            if ($attributeData[AttributeMetadataInterface::BACKEND_TYPE] != 'static'
                && $attributeData[AttributeMetadataInterface::IS_USED_IN_GRID]
                && $attributeData[AttributeMetadataInterface::OPTIONS]
            ) {
                $attributes[$attributeCode] = $attributeData[AttributeMetadataInterface::OPTIONS];
            }
        }
        return $attributes;
    }

    /**
     * Retrieve attribute option label by option value
     *
     * @param array $attributeData
     * @param string $value
     * @return string
     */
    protected function getAttributeOptionLabelByValue(array $attributeData, $value)
    {
        if (empty($value)) {
            return $value;
        }
        foreach ($attributeData as $option) {
            if ($option['value'] === $value) {
                return $option['label'];
            }
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = $this->searchResultToOutput($this->getSearchResult());
        $customAttributesOptions = $this->getCustomAttributesOptions();
        foreach ($customAttributesOptions as $attributeCode => $attributeData) {
            foreach ($data['items'] as &$item) {
                if (isset($item[$attributeCode])) {
                    $item[$attributeCode] = $this->getAttributeOptionLabelByValue(
                        $attributeData,
                        $item[$attributeCode]
                    );
                }
            }
        }
        return $data;
    }
}
