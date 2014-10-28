<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Config;

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
