<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManagerInterface;

class ScopeResolver implements ScopeResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $objectManager;

    /**
     * @var ScopeInterface
     */
    private $defaultScope;

    /**
     * ScopeResolver constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     * @return ScopeDefault
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
     */
    public function getScopes()
    {
        return [$this->defaultScope];
    }
}
