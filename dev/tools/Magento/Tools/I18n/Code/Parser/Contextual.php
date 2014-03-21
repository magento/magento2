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
 * Contextual Parser
 */
class Contextual extends AbstractParser
{
    /**
     * Context
     *
     * @var \Magento\Tools\I18n\Code\Context
     */
    protected $_context;

    /**
     * Parser construct
     *
     * @param Code\FilesCollector $filesCollector
     * @param Code\Factory $factory
     * @param Code\Context $context
     */
    public function __construct(Code\FilesCollector $filesCollector, Code\Factory $factory, Code\Context $context)
    {
        $this->_context = $context;

        parent::__construct($filesCollector, $factory);
    }

    /**
     * Parse one type
     *
     * @param array $options
     * @return void
     */
    protected function _parseByTypeOptions($options)
    {
        foreach ($this->_getFiles($options) as $file) {
            $adapter = $this->_adapters[$options['type']];
            $adapter->parse($file);

            list($contextType, $contextValue) = $this->_context->getContextByPath($file);

            foreach ($adapter->getPhrases() as $phraseData) {
                $this->_addPhrase($phraseData, $contextType, $contextValue);
            }
        }
    }

    /**
     * Add phrase with context
     *
     * @param array $phraseData
     * @param string $contextType
     * @param string $contextValue
     * @return void
     */
    protected function _addPhrase($phraseData, $contextType, $contextValue)
    {
        $phraseKey = $contextType . $phraseData['phrase'];

        if (isset($this->_phrases[$phraseKey])) {
            /** @var \Magento\Tools\I18n\Code\Dictionary\Phrase $phrase */
            $phrase = $this->_phrases[$phraseKey];
            $phrase->addContextValue($contextValue);
        } else {
            $this->_phrases[$phraseKey] = $this->_factory->createPhrase(
                array(
                    'phrase' => $phraseData['phrase'],
                    'translation' => $phraseData['phrase'],
                    'context_type' => $contextType,
                    'context_value' => array($contextValue),
                    'quote' => $phraseData['quote']
                )
            );
        }
    }
}
