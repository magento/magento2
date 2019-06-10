<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\ScopeInterface;

/**
 * Class StaticScope
 */
class StaticScope implements ScopeInterface
{
    /**
     * @var string
     */
    protected $scope;

    /**
     * StaticScope constructor.
     *
     * @param string $scope
     */
    public function __construct($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function getCurrentScope()
    {
        return $this->scope;
    }

    /**
     * Unused interface method
     *
     * @param string $scope
     */
    public function setCurrentScope($scope)
    {
        $this->scope = $scope;
    }
}
