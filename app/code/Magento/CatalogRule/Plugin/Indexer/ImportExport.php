<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Plugin\Indexer;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\ImportExport\Model\Import;

/**
 * Class \Magento\CatalogRule\Plugin\Indexer\ImportExport
 *
 * @since 2.0.0
 */
class ImportExport
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
     * @param Import $subject
     * @param bool $result
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterImportSource(Import $subject, $result)
    {
        if (!$this->ruleProductProcessor->isIndexerScheduled()) {
            $this->ruleProductProcessor->markIndexerAsInvalid();
        }
        return $result;
    }
}
