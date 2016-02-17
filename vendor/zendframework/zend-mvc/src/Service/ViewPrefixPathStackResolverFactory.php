<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Resolver\PrefixPathStackResolver;

class ViewPrefixPathStackResolverFactory implements FactoryInterface
{
    /**
     * Create the template prefix view resolver
     *
     * Creates a Zend\View\Resolver\PrefixPathStackResolver and populates it with the
     * ['view_manager']['prefix_template_path_stack']
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return PrefixPathStackResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config   = $serviceLocator->get('Config');
        $prefixes = array();

        if (isset($config['view_manager']['prefix_template_path_stack'])) {
            $prefixes = $config['view_manager']['prefix_template_path_stack'];
        }

        return new PrefixPathStackResolver($prefixes);
    }
}
