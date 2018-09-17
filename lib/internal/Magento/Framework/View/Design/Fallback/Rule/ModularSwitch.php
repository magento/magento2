<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

/**
 * Modular Switch
 *
 * Fallback rule that delegates execution to either modular or non-modular sub-rule depending on input parameters.
 */
class ModularSwitch implements RuleInterface
{
    /**
     * Rule non-modular
     *
     * @var RuleInterface
     */
    protected $ruleNonModular;

    /**
     * Rule modular
     *
     * @var RuleInterface
     */
    protected $ruleModular;

    /**
     * Constructor
     *
     * @param RuleInterface $ruleNonModular
     * @param RuleInterface $ruleModular
     */
    public function __construct(RuleInterface $ruleNonModular, RuleInterface $ruleModular)
    {
        $this->ruleNonModular = $ruleNonModular;
        $this->ruleModular = $ruleModular;
    }

    /**
     * Delegate execution to either modular or non-modular sub-rule depending on input parameters
     *
     * @param array $params
     * @return array
     */
    public function getPatternDirs(array $params)
    {
        if (isset($params['module_name'])) {
            return $this->ruleModular->getPatternDirs($params);
        } else {
            return $this->ruleNonModular->getPatternDirs($params);
        }
    }
}
