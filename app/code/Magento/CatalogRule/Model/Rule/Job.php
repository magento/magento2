<?php
/**
 * CatalogRule Rule Job model
 *
 * Uses for encapsulate some logic of rule model and for having ability change behavior (for example, in controller)
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogRule\Model\Rule;

use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;

/**
 * Catalog Rule job model
 *
 * @method \Magento\CatalogRule\Model\Rule\Job setSuccess(string $errorMessage)
 * @method \Magento\CatalogRule\Model\Rule\Job setError(string $errorMessage)
 * @method string getSuccess()
 * @method string getError()
 * @method bool hasSuccess()
 * @method bool hasError()
 *
 * @author Magento Core Team <core@magentocommerce.com>
 *
 * @api
 * @since 100.0.2
 */
class Job extends \Magento\Framework\DataObject
{
    /**
     * @var RuleProductProcessor
     */
    protected $ruleProcessor;

    /**
     * Basic object initialization
     *
     * @param RuleProductProcessor $ruleProcessor
     * @param array $data
     */
    public function __construct(
        RuleProductProcessor $ruleProcessor,
        array $data = []
    ) {
        $this->ruleProcessor = $ruleProcessor;
        parent::__construct($data);
    }

    /**
     * Dispatch event "catalogrule_apply_all" and set success or error message depends on result
     *
     * @return \Magento\CatalogRule\Model\Rule\Job
     * @api
     */
    public function applyAll()
    {
        try {
            $this->ruleProcessor->markIndexerAsInvalid();
            $this->setSuccess(__('Updated rules applied.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->setError($e->getMessage());
        }
        return $this;
    }
}
