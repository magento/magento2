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
namespace Magento\Core\Model\Url;

class ScopeResolver implements \Magento\Framework\Url\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var null|string
     */
    protected $_areaCode;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     * @param string|null $areaCode
     */
    public function __construct(\Magento\Framework\StoreManagerInterface $storeManager, $areaCode = null)
    {
        $this->_storeManager = $storeManager;
        $this->_areaCode = $areaCode;
    }

    /**
     * {@inheritdoc}
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getStore($scopeId);
        if (!$scope instanceof \Magento\Framework\Url\ScopeInterface) {
            throw new \Magento\Framework\Exception('Invalid scope object');
        }

        return $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function getScopes()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * {@inheritdoc}
     */
    public function getAreaCode()
    {
        return $this->_areaCode;
    }
}
