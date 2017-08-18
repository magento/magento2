<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config;

/**
 * Class \Magento\Framework\Config\Scope
 *
 */
class Scope implements \Magento\Framework\Config\ScopeInterface, \Magento\Framework\Config\ScopeListInterface
{
    /**
     * Default application scope
     *
     * @var string
     */
    protected $_defaultScope;

    /**
     * Current config scope
     *
     * @var string
     */
    protected $_currentScope;

    /**
     * List of all available areas
     *
     * @var \Magento\Framework\App\AreaList
     */
    protected $_areaList;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\AreaList $areaList
     * @param string $defaultScope
     */
    public function __construct(\Magento\Framework\App\AreaList $areaList, $defaultScope = 'primary')
    {
        $this->_defaultScope = $this->_currentScope = $defaultScope;
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
        array_unshift($codes, $this->_defaultScope);
        return $codes;
    }
}
