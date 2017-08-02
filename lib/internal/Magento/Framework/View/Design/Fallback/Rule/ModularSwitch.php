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
 * @since 2.0.0
 */
class ModularSwitch implements RuleInterface
{
    /**
     * Rule non-modular
     *
     * @var RuleInterface
     * @since 2.0.0
     */
    protected $ruleNonModular;

    /**
     * Rule modular
     *
     * @var RuleInterface
     * @since 2.0.0
     */
    protected $ruleModular;

    /**
     * Constructor
     *
     * @param RuleInterface $ruleNonModular
     * @param RuleInterface $ruleModular
     * @since 2.0.0
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
     * @since 2.0.0
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
