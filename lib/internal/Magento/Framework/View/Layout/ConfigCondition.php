<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\View\Layout\Condition\VisibilityConditionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ScopeResolverInterface;

/**
 * Check that config flag is set to true,
 * @since 2.2.0
 */
class ConfigCondition implements VisibilityConditionInterface
{
    /**
     * Unique name.
     */
    const NAME = 'ifconfig';

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    protected $scopeConfig;

    /**
     * @var ScopeResolverInterface
     * @since 2.2.0
     */
    protected $scopeResolver;

    /**
     * @var string|null
     * @since 2.2.0
     */
    private $scopeType;

    /**
     * ConfigCondition constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ScopeResolverInterface $scopeResolver
     * @param string|null $scopeType
     * @since 2.2.0
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
     * @inheritdoc
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function getName()
    {
        return self::NAME;
    }
}
