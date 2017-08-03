<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManagerInterface;

/**
 * Class \Magento\Framework\App\ScopeResolver
 *
 * @since 2.2.0
 */
class ScopeResolver implements ScopeResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.2.0
     */
    protected $objectManager;

    /**
     * @var ScopeInterface
     * @since 2.2.0
     */
    private $defaultScope;

    /**
     * ScopeResolver constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @since 2.2.0
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @return ScopeDefault
     * @since 2.2.0
     */
    public function getScope($scopeId = null)
    {
        if (!$this->defaultScope) {
            $this->defaultScope = $this->objectManager->create(ScopeDefault::class);
        }

        return $this->defaultScope;
    }

    /**
     * Retrieve a list of available scopes
     *
     * @return ScopeInterface[]
     * @since 2.2.0
     */
    public function getScopes()
    {
        return [$this->defaultScope];
    }
}
