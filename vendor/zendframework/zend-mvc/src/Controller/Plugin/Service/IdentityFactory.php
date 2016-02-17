<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Controller\Plugin\Service;

use Zend\Mvc\Controller\Plugin\Identity;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IdentityFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Zend\Mvc\Controller\Plugin\Identity
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $services = $serviceLocator->getServiceLocator();
        $helper = new Identity();
        if ($services->has('Zend\Authentication\AuthenticationService')) {
            $helper->setAuthenticationService($services->get('Zend\Authentication\AuthenticationService'));
        }
        return $helper;
    }
}
