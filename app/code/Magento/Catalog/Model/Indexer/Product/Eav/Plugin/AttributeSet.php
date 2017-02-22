<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

class AttributeSet
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $_indexerEavProcessor;

    /**
     * @var AttributeSet\IndexableAttributeFilter
     */
    protected $_attributeFilter;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor
     * @param AttributeSet\IndexableAttributeFilter $filter
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $indexerEavProcessor,
        AttributeSet\IndexableAttributeFilter $filter
    ) {
        $this->_indexerEavProcessor = $indexerEavProcessor;
        $this->_attributeFilter = $filter;
    }

    /**
     * Invalidate EAV indexer if attribute set has indexable attributes changes
     *
     * @param \Magento\Eav\Model\Entity\Attribute\Set $subject
     * @param callable $proceed
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(\Magento\Eav\Model\Entity\Attribute\Set $subject, \Closure $proceed)
    {
        $requiresReindex = false;
        if ($subject->getId()) {
            /** @var \Magento\Eav\Model\Entity\Attribute\Set $originalSet */
            $originalSet = clone $subject;
            $originalSet->initFromSkeleton($subject->getId());
            $originalAttributeCodes = array_flip($this->_attributeFilter->filter($originalSet));
            $subjectAttributeCodes = array_flip($this->_attributeFilter->filter($subject));
            $requiresReindex = (bool)count(array_merge(
                array_diff_key($subjectAttributeCodes, $originalAttributeCodes),
                array_diff_key($originalAttributeCodes, $subjectAttributeCodes)
            ));
        }
        $result = $proceed();
        if ($requiresReindex) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }
        return $result;
    }
}
