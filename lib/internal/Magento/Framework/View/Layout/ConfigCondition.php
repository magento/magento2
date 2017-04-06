<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Class ConfigCondition
 */
class ConfigCondition implements VisibilityConditionInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ScopeResolverInterface
     */
    protected $scopeResolver;

    /**
     * @var string|null
     */
    private $scopeType;

    /**
     * ConfigCondition constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ScopeResolverInterface $scopeResolver,
        $scopeType = null
    ) {
        $this->scopeType = $scopeType;
        $this->scopeConfig = $scopeConfig;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Validate logical condition for ui component
     * If validation passed block will be displayed
     *
     * @param array $arguments Attributes from element node.
     *
     * @return bool
     */
    public function isVisible(array $arguments)
    {
        return $this->scopeConfig->isSetFlag(
            $arguments['configPath'],
            $this->scopeType,
            $this->scopeResolver->getScope()
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ifconfig';
    }
}
