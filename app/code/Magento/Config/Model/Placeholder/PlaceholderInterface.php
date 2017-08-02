<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Placeholder;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Interface PlaceholderInterface
 * @api
 * @since 2.2.0
 */
interface PlaceholderInterface
{
    /**
     * Generating placeholder from value
     *
     * @param string $path
     * @param string $scopeType
     * @param string $scopeCode
     * @return string
     * @since 2.2.0
     */
    public function generate($path, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null);

    /**
     * Restoring path parts from template.
     *
     * @param string $template
     * @return string
     * @since 2.2.0
     */
    public function restore($template);

    /**
     * Check whether provided string is placeholder
     *
     * @param string $placeholder
     * @return bool
     * @since 2.2.0
     */
    public function isApplicable($placeholder);
}
