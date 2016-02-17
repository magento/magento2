<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\Mvc\HttpMethodListener;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class HttpMethodListenerFactory implements FactoryInterface
{
    /**
     * {@inheritdoc}
     * @return HttpMethodListener
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (! isset($config['http_methods_listener'])) {
            return new HttpMethodListener();
        }

        $listenerConfig  = $config['http_methods_listener'];
        $enabled = array_key_exists('enabled', $listenerConfig)
            ? $listenerConfig['enabled']
            : true;
        $allowedMethods = (isset($listenerConfig['allowed_methods']) && is_array($listenerConfig['allowed_methods']))
            ? $listenerConfig['allowed_methods']
            : null;

        return new HttpMethodListener($enabled, $allowedMethods);
    }
}
