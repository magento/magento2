<?php
/**
 * User role in role grid items updater.
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
namespace Magento\Webapi\Model\Acl\User;

class RoleUpdater implements \Magento\Core\Model\Layout\Argument\UpdaterInterface
{
    /**
     * @var int
     */
    protected $_userId;

    /**
     * @var \Magento\Webapi\Model\Acl\User\Factory
     */
    protected $_userFactory;

    /**
     * Constructor.
     *
     * @param \Magento\App\RequestInterface $request
     * @param \Magento\Webapi\Model\Acl\User\Factory $userFactory
     */
    public function __construct(
        \Magento\App\RequestInterface $request,
        \Magento\Webapi\Model\Acl\User\Factory $userFactory
    ) {
        $this->_userId = (int)$request->getParam('user_id');
        $this->_userFactory = $userFactory;
    }

    /**
     * Initialize value with role assigned to user.
     *
     * @param int|null $value
     * @return int|null
     */
    public function update($value)
    {
        if ($this->_userId) {
            $value = $this->_userFactory->create()->load($this->_userId)->getRoleId();
        }
        return $value;
    }
}
