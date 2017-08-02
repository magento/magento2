<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\Customer\Model\Group;

/**
 * Class \Magento\CatalogRule\Plugin\Indexer\CustomerGroup
 *
 * @since 2.0.0
 */
class CustomerGroup
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
     * @param Group $subject
     * @param Group $result
     * @return Group
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterDelete(
        Group $subject,
        Group $result
    ) {
        $this->ruleProductProcessor->markIndexerAsInvalid();
        return $result;
    }
}
