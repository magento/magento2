<?php
/**
 * Scan javascript files for invocations of mage.__() function, verifies that all the translations
 * were output to the page.
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Phrase;

use Magento\Tools\I18n\Parser\Adapter;
use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer;
use Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector;

class JsTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Tools\I18n\Parser\Adapter\Js
     */
    protected $_parser;

    /** @var \Magento\Framework\Test\Utility\Files  */
    protected $_utilityFiles;

    /** @var \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector */
    protected $_phraseCollector;

    protected function setUp()
    {
        $this->_parser = new \Magento\Tools\I18n\Parser\Adapter\Js();
        $this->_utilityFiles = \Magento\Framework\Test\Utility\Files::init();
        $this->_phraseCollector = new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Tools\I18n\Parser\Adapter\Php\Tokenizer()
        );
    }

    public function testGetPhrasesAdminhtml()
    {
        $unregisteredMessages = [];
        $untranslated = [];

        $registeredPhrases = $this->_getRegisteredPhrases();

        require_once BP . '/app/code/Magento/Backend/App/Area/FrontNameResolver.php';
        foreach ($this->_getJavascriptPhrases(\Magento\Backend\App\Area\FrontNameResolver::AREA_CODE) as $phrase) {
            if (!in_array($phrase['phrase'], $registeredPhrases)) {
                $unregisteredMessages[] = sprintf(
                    "'%s' \n in file %s, line# %s",
                    $phrase['phrase'],
                    $phrase['file'],
                    $phrase['line']
                );
                $untranslated[] = $phrase['phrase'];
            }
        }

        if (count($unregisteredMessages) > 0) {
            $this->fail(
                'There are UI messages in javascript files for adminhtml area ' .
                "which requires translations to be output to the page: \n\n" .
                implode(
                    "\n",
                    $unregisteredMessages
                )
            );
        }
    }

    public function testGetPhrasesFrontend()
    {
        $unregisteredMessages = [];
        $untranslated = [];

        $registeredPhrases = $this->_getRegisteredPhrases();

        foreach ($this->_getJavascriptPhrases('frontend') as $phrase) {
            if (!in_array($phrase['phrase'], $registeredPhrases)) {
                $unregisteredMessages[] = sprintf(
                    "'%s' \n in file %s, line# %s",
                    $phrase['phrase'],
                    $phrase['file'],
                    $phrase['line']
                );
                $untranslated[] = $phrase['phrase'];
            }
        }

        if (count($unregisteredMessages) > 0) {
            $this->fail(
                'There are UI messages in javascript files for frontend area ' .
                "which requires translations to be output to the page: \n\n" .
                implode(
                    "\n",
                    $unregisteredMessages
                )
            );
        }
    }

    /**
     * Returns an array of phrases that can be used by JS files.
     *
     * @return string[]
     */
    protected function _getRegisteredPhrases()
    {
        $jsHelperFile = realpath(
            __DIR__ . '/../../../../../../../../app/code/Magento/Translation/Model/Js/DataProvider.php'
        );

        $this->_phraseCollector->parse($jsHelperFile);

        $result = [];
        foreach ($this->_phraseCollector->getPhrases() as $phrase) {
            $result[] = stripcslashes(trim($phrase['phrase'], "'"));
        }
        return $result;
    }

    /**
     * Returns an array of phrases used by JavaScript files in a specific area of magento.
     *
     * @param string $area of magento to search, such as 'frontend' or 'adminthml'
     * @return string[]
     */
    protected function _getJavascriptPhrases($area)
    {
        $jsPhrases = [];
        foreach ($this->_utilityFiles->getJsFilesForArea($area) as $file) {
            $this->_parser->parse($file);
            $jsPhrases = array_merge($jsPhrases, $this->_parser->getPhrases());
        }
        return $jsPhrases;
    }
}
