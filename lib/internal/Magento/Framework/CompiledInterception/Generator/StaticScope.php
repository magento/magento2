<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\ScopeInterface;

class StaticScope implements ScopeInterface
{
    /**
     * @var string
     */
    protected $scope;

    public function __construct($scope)
    {
        $this->scope = $scope;
    }

    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function getCurrentScope() {
        return $this->scope;
    }

    /**
     * @param string $scope
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setCurrentScope($scope){
        throw new \Exception('readonly');
    }

}