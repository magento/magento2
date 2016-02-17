<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Zend\EventManager\ListenerAggregateInterface;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\ServiceManager\Exception\RuntimeException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FormAnnotationBuilderFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @throws \Zend\ServiceManager\Exception\RuntimeException
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        //setup a form factory which can use custom form elements
        $annotationBuilder = new AnnotationBuilder();
        $formElementManager = $serviceLocator->get('FormElementManager');
        $formElementManager->injectFactory($annotationBuilder);

        $config = $serviceLocator->get('Config');
        if (isset($config['form_annotation_builder'])) {
            $config = $config['form_annotation_builder'];

            if (isset($config['annotations'])) {
                foreach ((array) $config['annotations'] as $fullyQualifiedClassName) {
                    $annotationBuilder->getAnnotationParser()->registerAnnotation($fullyQualifiedClassName);
                }
            }

            if (isset($config['listeners'])) {
                foreach ((array) $config['listeners'] as $listenerName) {
                    $listener = $serviceLocator->get($listenerName);
                    if (!($listener instanceof ListenerAggregateInterface)) {
                        throw new RuntimeException(sprintf('Invalid event listener (%s) provided', $listenerName));
                    }
                    $listener->attach($annotationBuilder->getEventManager());
                }
            }

            if (isset($config['preserve_defined_order'])) {
                $annotationBuilder->setPreserveDefinedOrder($config['preserve_defined_order']);
            }
        }

        return $annotationBuilder;
    }
}
