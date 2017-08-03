<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Class \Magento\CatalogRule\Plugin\Indexer\Website
 *
 * @since 2.0.0
 */
class Website
{
    /**
     * @var RuleProductProcessor
     * @since 2.0.0
     */
    protected $ruleProductProcessor;

    /**
     * @param RuleProductProcessor $ruleProductProcessor
     * @since 2.0.0
     */
    public function __construct(RuleProductProcessor $ruleProductProcessor)
    {
        $this->ruleProductProcessor = $ruleProductProcessor;
    }

    /**
     * Invalidate catalog price rule indexer
     *
     * @param \Magento\Store\Model\Website $subject
     * @param \Magento\Store\Model\Website $result
     * @return \Magento\Store\Model\Website
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterDelete(
        \Magento\Store\Model\Website $subject,
        \Magento\Store\Model\Website $result
    ) {
        $this->ruleProductProcessor->markIndexerAsInvalid();
        return $result;
    }
}
