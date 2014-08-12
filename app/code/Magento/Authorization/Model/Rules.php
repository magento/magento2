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

namespace Magento\Authorization\Model;

/**
 * Admin Rules Model
 *
 * @method \Magento\Authorization\Model\Resource\Rules _getResource()
 * @method \Magento\Authorization\Model\Resource\Rules getResource()
 * @method int getRoleId()
 * @method \Magento\Authorization\Model\Rules setRoleId(int $value)
 * @method string getResourceId()
 * @method \Magento\Authorization\Model\Rules setResourceId(string $value)
 * @method string getPrivileges()
 * @method \Magento\Authorization\Model\Rules setPrivileges(string $value)
 * @method int getAssertId()
 * @method \Magento\Authorization\Model\Rules setAssertId(int $value)
 * @method string getPermission()
 * @method \Magento\Authorization\Model\Rules setPermission(string $value)
 */
class Rules extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Authorization\Model\Resource\Rules $resource
     * @param \Magento\Authorization\Model\Resource\Permissions\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Authorization\Model\Resource\Rules $resource,
        \Magento\Authorization\Model\Resource\Permissions\Collection $resourceCollection,
        array $data = array()
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Authorization\Model\Resource\Rules');
    }

    /**
     * @return $this
     */
    public function update()
    {
        $this->getResource()->update($this);
        return $this;
    }

    /**
     * @return $this
     */
    public function saveRel()
    {
        $this->getResource()->saveRel($this);
        return $this;
    }
}
