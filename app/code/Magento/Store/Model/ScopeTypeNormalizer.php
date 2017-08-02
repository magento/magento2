<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

/**
 * Normalizes scope types to the chosen form, plural or singular.
 * @since 2.2.0
 */
class ScopeTypeNormalizer
{
    /**
     * Normalizes scope types.
     *
     * We have a few forms of scope types in the plural and singular:
     * websites, website, groups, group, stores, store
     *
     * This method returns scope type in the chosen form (plural or singular).
     *
     * For example, the next calls of this method returns 'website':
     * ```php
     * $this->normalize('websites', false);
     * $this->normalize('website', false);
     * ```
     *
     * This calls of this method returns 'websites':
     * ```php
     * $this->normalize('website', false);
     * $this->normalize('websites', false);
     * $this->normalize('website');
     * $this->normalize('websites');
     * ```
     *
     * If there is not scope in the list (websites, website, groups, group, stores, store)
     * then it will be returned without changes.
     *
     * The next calls of this method returns 'default':
     * ```php
     * $this->normalize('default', false);
     * $this->normalize('default', true);
     * $this->normalize('default');
     * ```
     *
     * @param string $scopeType The type of scope
     * @param bool $plural The flag for choosing returned form of scope, in the plural or not. Used plural by default.
     * @return string
     * @since 2.2.0
     */
    public function normalize($scopeType, $plural = true)
    {
        $replaces = [
            ScopeInterface::SCOPE_WEBSITE => ScopeInterface::SCOPE_WEBSITES,
            ScopeInterface::SCOPE_GROUP => ScopeInterface::SCOPE_GROUPS,
            ScopeInterface::SCOPE_STORE => ScopeInterface::SCOPE_STORES,
        ];

        if (!$plural) {
            $replaces = array_flip($replaces);
        }

        return $replaces[$scopeType] ?? $scopeType;
    }
}
