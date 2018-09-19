<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Indexer\Product\Eav\Plugin;

use Magento\Eav\Model\Entity\Attribute\Set as EavAttributeSet;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Framework\App\ObjectManager;

class AttributeSet
{
    /**
     * @var bool
     */
    private $requiresReindex;

    /**
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Processor
     */
    private $_indexerEavProcessor;

    /**
     * @var AttributeSet\IndexableAttributeFilter
     */
    private $_attributeFilter;

    /**
     * Constructor
     *
     * @param Processor $indexerEavProcessor
     * @param AttributeSet\IndexableAttributeFilter $filter
     * @param SetFactory $attributeSetFactory
     */
    public function __construct(
        Processor $indexerEavProcessor,
        AttributeSet\IndexableAttributeFilter $filter,
        SetFactory $attributeSetFactory
    ) {
        $this->_indexerEavProcessor = $indexerEavProcessor;
        $this->_attributeFilter = $filter;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Check whether is needed to invalidate EAV indexer
     *
     * @param EavAttributeSet $subject
     *
     * @return void
     */
    public function beforeSave(EavAttributeSet $subject)
    {
        $this->requiresReindex = false;
        if ($subject->getId()) {
            /** @var EavAttributeSet $originalSet */
            $originalSet = $this->attributeSetFactory->create();
            $originalSet->initFromSkeleton($subject->getId());
            $originalAttributeCodes = array_flip($this->_attributeFilter->filter($originalSet));
            $subjectAttributeCodes  = array_flip($this->_attributeFilter->filter($subject));
            $this->requiresReindex  = (bool)count(
                array_merge(
                    array_diff_key($subjectAttributeCodes, $originalAttributeCodes),
                    array_diff_key($originalAttributeCodes, $subjectAttributeCodes)
                )
            );
        }
    }

    /**
     * Invalidate EAV indexer if attribute set has indexable attributes changes
     *
     * @param EavAttributeSet $subject
     * @param EavAttributeSet $result
     * @return EavAttributeSet
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(EavAttributeSet $subject, EavAttributeSet $result)
    {
        if ($this->requiresReindex) {
            $this->_indexerEavProcessor->markIndexerAsInvalid();
        }
        return $result;
    }
}
