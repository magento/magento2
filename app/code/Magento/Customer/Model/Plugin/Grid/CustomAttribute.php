<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Model\Plugin\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Customer\Api\Data\AttributeMetadataInterface;

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

    public function getCustomAttributesWithOptions()
    {
        $attributes = [];
        foreach ($this->attributeRepository->getList() as $attribute) {
            if ($attribute->getBackendType() != 'static' && $attribute->getIsUsedInGrid() && $attribute->getOptions()) {
                $attributes[] = $attribute;
            }
        }
        return $attributes;
    }

    protected function getAttributeOptionLabelById(AttributeMetadataInterface $attribute, $optionId)
    {
        $optionLabel = '';
        foreach ($attribute->getOptions() as $option) {
            if ($option->getValue() === $optionId) {
                $optionLabel = $option->getLabel();
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
        /** @var AttributeMetadataInterface $attribute */
        foreach ($this->getCustomAttributesWithOptions() as $attribute) {
            /** @var \Magento\Framework\View\Element\UiComponent\DataProvider\Document $document */
            foreach ($documents as $document) {
                $optionId = $document->getData($attribute->getAttributeCode());
                if ($optionId) {
                    $document->setData(
                        $attribute->getAttributeCode(),
                        $this->getAttributeOptionLabelById($attribute, $optionId)
                    );
                }
            }
        }

        return $documents;
    }
}
