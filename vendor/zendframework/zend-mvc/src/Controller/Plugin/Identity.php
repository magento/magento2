<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Mvc\Exception;

/**
 * Controller plugin to fetch the authenticated identity.
 */
class Identity extends AbstractPlugin
{
    /**
     * @var AuthenticationServiceInterface
     */
    protected $authenticationService;

    /**
     * @return AuthenticationServiceInterface
     */
    public function getAuthenticationService()
    {
        return $this->authenticationService;
    }

    /**
     * @param AuthenticationServiceInterface $authenticationService
     */
    public function setAuthenticationService(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Retrieve the current identity, if any.
     *
     * If none is present, returns null.
     *
     * @return mixed|null
     * @throws Exception\RuntimeException
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
}
