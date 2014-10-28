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
namespace Magento\Tools\I18n\Code\Parser;

use Magento\Tools\I18n\Code;

/**
 * Abstract parser
 */
abstract class AbstractParser implements Code\ParserInterface
{
    /**
     * Files collector
     *
     * @var \Magento\Tools\I18n\Code\FilesCollector
     */
    protected $_filesCollector = array();

    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
     */
    protected $_factory;

    /**
     * Adapters
     *
     * @var \Magento\Tools\I18n\Code\Parser\AdapterInterface[]
     */
    protected $_adapters = array();

    /**
     * Parsed phrases
     *
     * @var array
     */
    protected $_phrases = array();

    /**
     * Parser construct
     *
     * @param Code\FilesCollector $filesCollector
     * @param Code\Factory $factory
     */
    public function __construct(Code\FilesCollector $filesCollector, Code\Factory $factory)
    {
        $this->_filesCollector = $filesCollector;
        $this->_factory = $factory;
    }

    /**
     * Add parser
     *
     * @param string $type
     * @param AdapterInterface $adapter
     * @return void
     */
    public function addAdapter($type, AdapterInterface $adapter)
    {
        $this->_adapters[$type] = $adapter;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $parseOptions)
    {
        $this->_validateOptions($parseOptions);

        foreach ($parseOptions as $typeOptions) {
            $this->_parseByTypeOptions($typeOptions);
        }
        return $this->_phrases;
    }

    /**
     * Parse one type
     *
     * @param array $options
     * @return void
     */
    abstract protected function _parseByTypeOptions($options);

    /**
     * Validate options
     *
     * @param array $parseOptions
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _validateOptions($parseOptions)
    {
        foreach ($parseOptions as $parserOptions) {
            if (empty($parserOptions['type'])) {
                throw new \InvalidArgumentException('Missed "type" in parser options.');
            }
            if (!isset($this->_adapters[$parserOptions['type']])) {
                throw new \InvalidArgumentException(
                    sprintf('Adapter is not set for type "%s".', $parserOptions['type'])
                );
            }
            if (!isset($parserOptions['paths']) || !is_array($parserOptions['paths'])) {
                throw new \InvalidArgumentException('"paths" in parser options must be array.');
            }
        }
    }

    /**
     * Get files for parsing
     *
     * @param array $options
     * @return array
     */
    protected function _getFiles($options)
    {
        $fileMask = isset($options['fileMask']) ? $options['fileMask'] : '';

        return $this->_filesCollector->getFiles($options['paths'], $fileMask);
    }

    /**
     * {@inheritdoc}
     */
    public function getPhrases()
    {
        return $this->_phrases;
    }
}
