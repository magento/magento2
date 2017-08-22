<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Placeholder;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DeploymentConfig;

/**
 * Class is used to work with placeholders for environment variables names based on config paths
 * @api
 * @since 100.1.2
 */
class Environment implements PlaceholderInterface
{
    /**
     * @const string Prefix for placeholder
     */
    const PREFIX = 'CONFIG__';

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     * @since 100.1.2
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Generates placeholder like CONFIG__DEFAULT__TEST__TEST_VALUE
     *
     * @inheritdoc
     * @since 100.1.2
     */
    public function generate($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $parts = $scopeType ? [$scopeType] : [];

        if ($scopeType !== ScopeConfigInterface::SCOPE_TYPE_DEFAULT && $scopeCode) {
            $parts[] = $scopeCode;
        }

        $parts[] = $path;

        $template = implode('__', $parts);
        $template = str_replace('/', '__', $template);
        $template = static::PREFIX . $template;
        $template = strtoupper($template);

        return $template;
    }

    /**
     * @inheritdoc
     * @since 100.1.2
     */
    public function restore($template)
    {
        $template = preg_replace('/^' . static::PREFIX . '/', '', $template);
        $template = str_replace('__', '/', $template);
        $template = strtolower($template);

        return $template;
    }

    /**
     * @inheritdoc
     * @since 100.1.2
     */
    public function isApplicable($placeholder)
    {
        return 1 === preg_match('/^' . static::PREFIX . '([a-zA-Z]+)([a-zA-Z0-9_])*$/', $placeholder);
    }
}
