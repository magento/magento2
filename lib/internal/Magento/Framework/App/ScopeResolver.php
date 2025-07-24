<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App;

use Magento\Framework\ObjectManagerInterface;

class ScopeResolver implements ScopeResolverInterface
{
    /**
     * @var ObjectManagerInterface
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
     * @inheritdoc
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
