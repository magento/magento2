<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\View\Exception;

/**
 * View helper plugin to fetch the authenticated identity.
 */
class Identity extends AbstractHelper
{
    /**
     * AuthenticationService instance
     *
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;

    /**
     * Retrieve the current identity, if any.
     *
     * If none available, returns null.
     *
     * @throws Exception\RuntimeException
     * @return mixed|null
     */
    public function __invoke()
    {
        if (!$this->authenticationService instanceof AuthenticationServiceInterface) {
            throw new Exception\RuntimeException('No AuthenticationServiceInterface instance provided');
        }

        if (!$this->authenticationService->hasIdentity()) {
            return;
        }

        return $this->authenticationService->getIdentity();
    }

    /**
     * Set AuthenticationService instance
     *
     * @param AuthenticationServiceInterface $authenticationService
     * @return Identity
     */
    public function setAuthenticationService(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
        return $this;
    }

    /**
     * Get AuthenticationService instance
     *
     * @return AuthenticationServiceInterface
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }
}
