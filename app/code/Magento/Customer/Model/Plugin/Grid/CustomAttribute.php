<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Api\Data\AttributeMetadataInterface as AttributeMetadata;

class CustomAttribute
{
    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(
        AttributeRepository $attributeRepository
    ) {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @return array
     */
    public function getCustomAttributesWithOptions()
    {
        $attributes = [];
        foreach ($this->attributeRepository->getList() as $attributeCode => $attributeData) {
            if ($attributeData[AttributeMetadata::BACKEND_TYPE] != 'static'
                && $attributeData[AttributeMetadata::IS_USED_IN_GRID]
                && $attributeData[AttributeMetadata::OPTIONS]
            ) {
                $attributes[$attributeCode] = $attributeData;
            }
        }
        return $attributes;
    }

    /**
     * @param array $attributeData
     * @param string $optionId
     * @return string
     */
    protected function getAttributeOptionLabelById(array $attributeData, $optionId)
    {
        $optionLabel = '';
        foreach ($attributeData[AttributeMetadata::OPTIONS] as $option) {
            if ($option['value'] === $optionId) {
                $optionLabel = $option['label'];
                break;
            }
        }
        return $optionLabel;
    }

    /**
     * @param SearchResult $subject
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\Document[] $documents
     * @return \Magento\Framework\View\Element\UiComponent\DataProvider\Document[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItems(SearchResult $subject, $documents)
    {
        foreach ($this->getCustomAttributesWithOptions() as $attributeCode => $attributeData) {
            /** @var \Magento\Framework\View\Element\UiComponent\DataProvider\Document $document */
            foreach ($documents as $document) {
                $optionId = $document->getData($attributeCode);
                if ($optionId) {
                    $document->setData(
                        $attributeCode,
                        $this->getAttributeOptionLabelById($attributeData, $optionId)
                    );
                }
            }
        }

        return $documents;
    }
}
