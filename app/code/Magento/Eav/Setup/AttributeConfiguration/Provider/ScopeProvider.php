<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Setup\AttributeConfiguration\Provider;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\AttributeConfiguration\InvalidConfigurationException;

class ScopeProvider implements ProviderInterface
{
    /**
     * @var array
     */
    private $validScopes = [
        ScopedAttributeInterface::SCOPE_GLOBAL,
        ScopedAttributeInterface::SCOPE_WEBSITE,
        ScopedAttributeInterface::SCOPE_STORE,
    ];

    /**
     * {@inheritdoc}
     */
    public function exists($storeScope)
    {
        return array_search($storeScope, $this->validScopes, true) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($storeScope)
    {
        if (!$this->exists($storeScope)) {
            throw new InvalidConfigurationException(__('Store scope "%1" is not supported', $storeScope));
        }

        return $storeScope;
    }
}
