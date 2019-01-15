<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Fallback\Rule;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;

/**
 * Fallback Rule Module
 *
 * Propagates all parameters necessary for modular rule
 */
class Module implements RuleInterface
{
    /**
     * Rule
     *
     * @var RuleInterface
     */
    protected $rule;

    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * Constructors
     *
     * @param RuleInterface $rule
     * @param ComponentRegistrarInterface $componentRegistrar
     */
    public function __construct(RuleInterface $rule, ComponentRegistrarInterface $componentRegistrar)
    {
        $this->rule = $rule;
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * Propagate parameters necessary for modular rule basing on module_name parameter
     *
     * @param array $params
     * @return array
     * @throws \InvalidArgumentException
     */
    public function getPatternDirs(array $params)
    {
        if (!array_key_exists('module_name', $params)) {
            throw new \InvalidArgumentException(
                'Required parameter "module_name" is not specified.'
            );
        }
        $params['module_dir'] = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            $params['module_name']
        );
        if (empty($params['module_dir'])) {
            return [];
        }
        return $this->rule->getPatternDirs($params);
    }
}
