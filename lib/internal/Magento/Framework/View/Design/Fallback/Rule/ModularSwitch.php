<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params)
    {
        $isNamespaceDefined = isset($params['namespace']);
        $isModuleDefined = isset($params['module']);
        if ($isNamespaceDefined && $isModuleDefined) {
            return $this->ruleModular->getPatternDirs($params);
        } elseif (!$isNamespaceDefined && !$isModuleDefined) {
            return $this->ruleNonModular->getPatternDirs($params);
        }
        throw new \InvalidArgumentException("Parameters 'namespace' and 'module' should either be both set or unset.");
    }
}
