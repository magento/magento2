<?php

namespace Liip\CustomerHierarchy\Model\Plugin;

use \Liip\CustomerHierarchy\Model\AccountForbiddenException;

class AccountManagement
{
    /**
     * @var \Liip\CustomerHierarchy\Model\Role\Permission\Checker\PoolInterface
     */
    private $permissionCheckerPool;

    /**
     * @param \Liip\CustomerHierarchy\Model\Role\Permission\Checker\PoolInterface $permissionCheckerPool
     */
    public function __construct(
        \Liip\CustomerHierarchy\Model\Role\Permission\Checker\PoolInterface $permissionCheckerPool
    ) {
        $this->permissionCheckerPool = $permissionCheckerPool;
    }

    /**
     * @param \Magento\Customer\Model\AccountManagement $subject
     * @param \Closure $proceed
     * @param string $username
     * @param string $password
     * @return mixed
     * @throws AccountForbiddenException
     */
    public function aroundAuthenticate(
        \Magento\Customer\Model\AccountManagement $subject,
        \Closure $proceed,
        $username,
        $password
    ) {
        if (!$this->permissionCheckerPool->get('login')->isAllowed()) {
            throw new AccountForbiddenException(__('Account has not enough permissions.'));
        }
        return $proceed($username, $password);
    }
}
