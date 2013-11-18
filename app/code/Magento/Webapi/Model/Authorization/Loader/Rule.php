<?php
/**
 * API ACL Rule Loader
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Model\Authorization\Loader;

class Rule implements \Magento\Acl\LoaderInterface
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Rule
     */
    protected $_ruleResource;

    /**
     * @param \Magento\Webapi\Model\Resource\Acl\Rule $ruleResource
     */
    public function __construct(\Magento\Webapi\Model\Resource\Acl\Rule $ruleResource)
    {
        $this->_ruleResource = $ruleResource;
    }

    /**
     * Populate ACL with rules from external storage.
     *
     * @param \Magento\Acl $acl
     */
    public function populateAcl(\Magento\Acl $acl)
    {
        $ruleList = $this->_ruleResource->getRuleList();
        foreach ($ruleList as $rule) {
            $role = $rule['role_id'];
            $resource = $rule['resource_id'];
            if ($acl->hasRole($role) && $acl->has($resource)) {
                $acl->allow($role, $resource);
            }
        }
    }
}
