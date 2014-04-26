<?php
/**
 * Uses ACL to control access. If ACL doesn't contain provided resource,
 * permission for all resources is checked
 *
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
namespace Magento\Framework\Authorization\Policy;

use Magento\Framework\Acl\Builder;
use Magento\Framework\Authorization\Policy;

class Acl implements Policy
{
    /**
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @param Builder $aclBuilder
     */
    public function __construct(Builder $aclBuilder)
    {
        $this->_aclBuilder = $aclBuilder;
    }

    /**
     * Check whether given role has access to give id
     *
     * @param string $roleId
     * @param string $resourceId
     * @param string $privilege
     * @return bool
     */
    public function isAllowed($roleId, $resourceId, $privilege = null)
    {
        try {
            return $this->_aclBuilder->getAcl()->isAllowed($roleId, $resourceId, $privilege);
        } catch (\Exception $e) {
            try {
                if (!$this->_aclBuilder->getAcl()->has($resourceId)) {
                    return $this->_aclBuilder->getAcl()->isAllowed($roleId, null, $privilege);
                }
            } catch (\Exception $e) {
            }
        }
        return false;
    }
}
