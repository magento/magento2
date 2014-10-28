<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
