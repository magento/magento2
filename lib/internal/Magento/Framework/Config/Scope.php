<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

use Magento\Framework\App\AreaList;

/**
 * Scope config
 */
class Scope implements ScopeInterface, ScopeListInterface
{
    /**
     * Current config scope
     *
     * @var string
     */
    protected $_currentScope;

    /**
     * List of all available areas
     *
     * @var AreaList
     */
    protected $_areaList;

    /**
     * Constructor
     *
     * @param AreaList $areaList
     * @param string $defaultScope
     */
    public function __construct(AreaList $areaList, $defaultScope = 'primary')
    {
        $this->_currentScope = $defaultScope;
        $this->_areaList = $areaList;
    }

    /**
     * Get current configuration scope identifier
     *
     * @return string
     */
    public function getCurrentScope()
    {
        return $this->_currentScope;
    }

    /**
     * Set current configuration scope
     *
     * @param string $scope
     * @return void
     */
    public function setCurrentScope($scope)
    {
        $this->_currentScope = $scope;
    }

    /**
     * Retrieve list of available config scopes
     *
     * @return string[]
     */
    public function getAllScopes()
    {
        $codes = $this->_areaList->getCodes();
        array_unshift($codes, 'global');
        array_unshift($codes, 'primary');

        return $codes;
    }
}
