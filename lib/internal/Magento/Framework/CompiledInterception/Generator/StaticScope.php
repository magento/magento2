<?php
/**
 * Created by PhpStorm.
 * User: fwawrzak
 * Date: 15.05.19
 * @license http://creatuity.com/license
 * @copyright Copyright (c) 2008-2017 Creatuity Corp. (http://www.creatuity.com)
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
     */
    public function setCurrentScope($scope){
        throw new \Exception('readonly');
    }

}