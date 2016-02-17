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
use Zend\View\Resolver as ViewResolver;

class ViewResolverFactory implements FactoryInterface
{
    /**
     * Create the aggregate view resolver
     *
     * Creates a Zend\View\Resolver\AggregateResolver and attaches the template
     * map resolver and path stack resolver
     *
     * @param  ServiceLocatorInterface        $serviceLocator
     * @return ViewResolver\AggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $resolver = new ViewResolver\AggregateResolver();

        /* @var $mapResolver \Zend\View\Resolver\ResolverInterface */
        $mapResolver             = $serviceLocator->get('ViewTemplateMapResolver');
        /* @var $pathResolver \Zend\View\Resolver\ResolverInterface */
        $pathResolver            = $serviceLocator->get('ViewTemplatePathStack');
        /* @var $prefixPathStackResolver \Zend\View\Resolver\ResolverInterface */
        $prefixPathStackResolver = $serviceLocator->get('ViewPrefixPathStackResolver');

        $resolver
            ->attach($mapResolver)
            ->attach($pathResolver)
            ->attach($prefixPathStackResolver)
            ->attach(new ViewResolver\RelativeFallbackResolver($mapResolver))
            ->attach(new ViewResolver\RelativeFallbackResolver($pathResolver))
            ->attach(new ViewResolver\RelativeFallbackResolver($prefixPathStackResolver));

        return $resolver;
    }
}
