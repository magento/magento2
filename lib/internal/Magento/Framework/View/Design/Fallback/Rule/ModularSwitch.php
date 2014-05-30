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
