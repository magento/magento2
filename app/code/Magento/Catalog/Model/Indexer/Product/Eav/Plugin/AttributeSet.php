<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
    private $setFactory;

    /**
     * @var Processor
     */
    protected $_indexerEavProcessor;

    /**
     * @var AttributeSet\IndexableAttributeFilter
     */
    protected $_attributeFilter;

    /**
     * @param Processor $indexerEavProcessor
     * @param AttributeSet\IndexableAttributeFilter $filter
     */
    public function __construct(Processor $indexerEavProcessor, AttributeSet\IndexableAttributeFilter $filter)
    {
        $this->_indexerEavProcessor = $indexerEavProcessor;
        $this->_attributeFilter = $filter;
    }

    /**
     * Return attribute set factory
     *
     * @return SetFactory
     * @deprecated
     */
    private function getAttributeSetFactory()
    {
        if ($this->setFactory === null) {
            $this->setFactory = ObjectManager::getInstance()->get(SetFactory::class);
        }
        return $this->setFactory;
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
            $originalSet = $this->getAttributeSetFactory()->create();
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
