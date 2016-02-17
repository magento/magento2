<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Traversable;
use Zend\I18n\Translator\Translator;
use Zend\Mvc\I18n\DummyTranslator;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Overrides the translator factory from the i18n component in order to
 * replace it with the bridge class from this namespace.
 */
class TranslatorServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return MvcTranslator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Assume that if a user has registered a service for the
        // TranslatorInterface, it must be valid
        if ($serviceLocator->has('Zend\I18n\Translator\TranslatorInterface')) {
            return new MvcTranslator($serviceLocator->get('Zend\I18n\Translator\TranslatorInterface'));
        }

        // Load a translator from configuration, if possible
        if ($serviceLocator->has('Config')) {
            $config = $serviceLocator->get('Config');

            // 'translator' => false
            if (array_key_exists('translator', $config) && $config['translator'] === false) {
                return new MvcTranslator(new DummyTranslator());
            }

            // 'translator' => array( ... translator options ... )
            if (array_key_exists('translator', $config)
                && ((is_array($config['translator']) && !empty($config['translator']))
                    || $config['translator'] instanceof Traversable)
            ) {
                $i18nTranslator = Translator::factory($config['translator']);
                $i18nTranslator->setPluginManager($serviceLocator->get('TranslatorPluginManager'));
                $serviceLocator->setService('Zend\I18n\Translator\TranslatorInterface', $i18nTranslator);
                return new MvcTranslator($i18nTranslator);
            }
        }

        // If ext/intl is not loaded, return a dummy translator
        if (!extension_loaded('intl')) {
            return new MvcTranslator(new DummyTranslator());
        }

        // For BC purposes (pre-2.3.0), use the I18n Translator
        return new MvcTranslator(new Translator());
    }
}
