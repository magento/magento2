<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tools\I18n\Code;

use Magento\Tools\I18n\Code\Parser;
use Magento\Tools\I18n\Code\Dictionary;
use Magento\Tools\I18n\Code\Pack;

/**
 *  Service Locator (instead DI container)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceLocator
{
    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
     */
    private static $_factory;

    /**
     * Context manager
     *
     * @var \Magento\Tools\I18n\Code\Factory
     */
    private static $_context;

    /**
     * Dictionary generator
     *
     * @var \Magento\Tools\I18n\Code\Dictionary\Generator
     */
    private static $_dictionaryGenerator;

    /**
     * Pack generator
     *
     * @var \Magento\Tools\I18n\Code\Pack\Generator
     */
    private static $_packGenerator;

    /**
     * Get dictionary generator
     *
     * @return \Magento\Tools\I18n\Code\Dictionary\Generator
     */
    public static function getDictionaryGenerator()
    {
        if (null === self::$_dictionaryGenerator) {
            $filesCollector = new FilesCollector();

            $phraseCollector = new Parser\Adapter\Php\Tokenizer\PhraseCollector(new Parser\Adapter\Php\Tokenizer());
            $adapters = array(
                'php' => new Parser\Adapter\Php($phraseCollector),
                'js' => new Parser\Adapter\Js(),
                'xml' => new Parser\Adapter\Xml()
            );

            $parser = new Parser\Parser($filesCollector, self::_getFactory());
            $parserContextual = new Parser\Contextual($filesCollector, self::_getFactory(), self::_getContext());
            foreach ($adapters as $type => $adapter) {
                $parser->addAdapter($type, $adapter);
                $parserContextual->addAdapter($type, $adapter);
            }

            self::$_dictionaryGenerator = new Dictionary\Generator(
                $parser,
                $parserContextual,
                self::_getFactory(),
                new Dictionary\Options\ResolverFactory
            );
        }
        return self::$_dictionaryGenerator;
    }

    /**
     * Get pack generator
     *
     * @return \Magento\Tools\I18n\Code\Pack\Generator
     */
    public static function getPackGenerator()
    {
        if (null === self::$_packGenerator) {
            $dictionaryLoader = new Dictionary\Loader\File\Csv(self::_getFactory());
            $packWriter = new Pack\Writer\File\Csv(self::_getContext(), $dictionaryLoader, self::_getFactory());

            self::$_packGenerator = new Pack\Generator($dictionaryLoader, $packWriter, self::_getFactory());
        }
        return self::$_packGenerator;
    }

    /**
     * Get factory
     *
     * @return \Magento\Tools\I18n\Code\Factory
     */
    private static function _getFactory()
    {
        if (null === self::$_factory) {
            self::$_factory = new \Magento\Tools\I18n\Code\Factory();
        }
        return self::$_factory;
    }

    /**
     * Get context
     *
     * @return \Magento\Tools\I18n\Code\Context
     */
    private static function _getContext()
    {
        if (null === self::$_context) {
            self::$_context = new Context();
        }
        return self::$_context;
    }
}
