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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Store\Model\Resolver;

class Website implements \Magento\Framework\App\ScopeResolverInterface
{
    /**
     * @var \Magento\Framework\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     * @throws \Magento\Framework\App\InitException
     */
    public function getScope($scopeId = null)
    {
        $scope = $this->_storeManager->getWebsite($scopeId);
        if (!($scope instanceof \Magento\Framework\App\ScopeInterface)) {
            throw new \Magento\Framework\App\InitException('Invalid scope object');
        }

        return $scope;
    }
}
