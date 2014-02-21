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

namespace Magento\Tools\I18n\Code\Dictionary;

use Magento\Tools\I18n\Code\Factory;
use Magento\Tools\I18n\Code\ParserInterface;

/**
 * Dictionary generator
 */
class Generator
{
    /**
     * Parser
     *
     * @var \Magento\Tools\I18n\Code\ParserInterface
     */
    protected $_parser;

    /**
     * Contextual parser
     *
     * @var \Magento\Tools\I18n\Code\ParserInterface
     */
    protected $_contextualParser;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
     */
    protected $_factory;

    /**
     * Generator construct
     *
     * @param \Magento\Tools\I18n\Code\ParserInterface $parser
     * @param \Magento\Tools\I18n\Code\ParserInterface $contextualParser
     * @param \Magento\Tools\I18n\Code\Factory $factory
     */
    public function __construct(ParserInterface $parser, ParserInterface $contextualParser, Factory $factory)
    {
        $this->_parser = $parser;
        $this->_contextualParser = $contextualParser;
        $this->_factory = $factory;
    }

    /**
     * Generate dictionary
     *
     * @param array $filesOptions
     * @param string $outputFilename
     * @param bool $withContext
     */
    public function generate(array $filesOptions, $outputFilename, $withContext = false)
    {
        $writer = $this->_factory->createDictionaryWriter($outputFilename);

        $parser = $this->_getActualParser($withContext);
        $parser->parse($filesOptions);

        foreach ($parser->getPhrases() as $phrase) {
            $writer->write($phrase);
        }
    }

    /**
     * Get actual parser
     *
     * @param bool $withContext
     * @return \Magento\Tools\I18n\Code\ParserInterface
     */
    protected function _getActualParser($withContext)
    {
        return $withContext ? $this->_contextualParser : $this->_parser;
    }
}
