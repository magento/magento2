<?php
/**
 * Web API ACL Rules.
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
 *
 * @method int getRoleId() getRoleId()
 * @method \Magento\Webapi\Model\Acl\Rule setRoleId() setRoleId(int $value)
 * @method string getResourceId() getResourceId()
 * @method \Magento\Webapi\Model\Resource\Acl\Rule getResource() getResource()
 * @method \Magento\Webapi\Model\Resource\Acl\Rule\Collection getCollection() getCollection()
 * @method \Magento\Webapi\Model\Acl\Rule setResourceId() setResourceId(string $value)
 * @method \Magento\Webapi\Model\Acl\Rule setResources() setResources(array $resources)
 * @method array getResources() getResources()
 */
namespace Magento\Webapi\Model\Acl;

class Rule extends \Magento\Core\Model\AbstractModel
{
    /**
     * Web API ACL resource separator.
     */
    const RESOURCE_SEPARATOR = '/';

    /**
     * Constructor.
     */
    protected function _construct()
    {
        $this->_init('Magento\Webapi\Model\Resource\Acl\Rule');
    }

    /**
     * Save role resources.
     *
     * @return \Magento\Webapi\Model\Acl\Rule
     */
    public function saveResources()
    {
        $this->getResource()->saveResources($this);
        return $this;
    }
}
