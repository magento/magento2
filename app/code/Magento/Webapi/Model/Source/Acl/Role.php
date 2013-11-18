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
 * @category    Magento
 * @package     Magento_Webapi
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Web API Role source model.
 *
 * @category    Magento
 * @package     Magento_Webapi
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Webapi\Model\Source\Acl;

class Role implements \Magento\Core\Model\Option\ArrayInterface
{
    /**
     * @var \Magento\Webapi\Model\Resource\Acl\Role
     */
    protected $_resource = null;

    /**
     * @param \Magento\Webapi\Model\Resource\Acl\RoleFactory $roleFactory
     */
    public function __construct(
        \Magento\Webapi\Model\Resource\Acl\RoleFactory $roleFactory
    ) {
        $this->_resource = $roleFactory->create();
    }

    /**
     * Retrieve option hash of Web API Roles.
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionHash($addEmpty = true)
    {
        $options = $this->_resource->getRolesList();
        if ($addEmpty) {
            $options = array('' => '') + $options;
        }
        return $options;
    }

    /**
     * Return option array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_resource->getRolesList();
        return $options;
    }
}
