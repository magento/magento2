<?php
/**
 * Users in role grid items updater.
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
namespace Magento\Webapi\Model\Acl\Role;

class UsersUpdater implements \Magento\Core\Model\Layout\Argument\UpdaterInterface
{
    /**
     * Filter name for users by role.
     */
    const IN_ROLE_USERS_PARAMETER = 'in_role_users';

    /**#@+
     * Supported values of filtering users by role.
     */
    const IN_ROLE_USERS_ANY = 1;
    const IN_ROLE_USERS_YES = 2;
    const IN_ROLE_USERS_NO = 3;
    /**#@-*/

    /**
     * @var int
     */
    protected $_roleId;

    /**
     * @var \Magento\App\RequestInterface
     */
    protected $_inRoleUsersFilter;

    /**
     * Constructor.
     *
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Backend\Helper\Data $backendHelper
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $this->_roleId = (int)$request->getParam('role_id');
        $this->_inRoleUsersFilter = $this->_parseInRoleUsersFilter($request, $backendHelper);
    }

    /**
     * Parse $_inRoleUsersFilter value from request
     *
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @return int
     */
    protected function _parseInRoleUsersFilter(
        \Magento\App\RequestInterface $request,
        \Magento\Backend\Helper\Data $backendHelper
    ) {
        $result = self::IN_ROLE_USERS_ANY;
        $filter = $backendHelper->prepareFilterString($request->getParam('filter', ''));
        if (isset($filter[self::IN_ROLE_USERS_PARAMETER])) {
            $result = $filter[self::IN_ROLE_USERS_PARAMETER] ? self::IN_ROLE_USERS_YES : self::IN_ROLE_USERS_NO;
        } elseif (!$request->isAjax()) {
            $result = self::IN_ROLE_USERS_YES;
        }
        return $result;
    }

    /**
     * Add filtering users by role.
     *
     * @param \Magento\Webapi\Model\Resource\Acl\User\Collection $collection
     * @return \Magento\Webapi\Model\Resource\Acl\User\Collection
     */
    public function update($collection)
    {
        if ($this->_roleId) {
            switch ($this->_inRoleUsersFilter) {
                case self::IN_ROLE_USERS_YES:
                    $collection->addFieldToFilter('role_id', $this->_roleId);
                    break;
                case self::IN_ROLE_USERS_NO:
                    $collection->addFieldToFilter('role_id', array(
                        array('neq' => $this->_roleId),
                        array('is' => new \Zend_Db_Expr('NULL'))
                    ));
                    break;
            }
        }
        return $collection;
    }
}
