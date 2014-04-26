<?php
/**
 * Scan javascript files for invocations of mage.__() function, verifies that all the translations
 * were output to the page.
 *
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
namespace Magento\Test\Integrity\Phrase;

use Magento\Tools\I18n\Code\Parser\Adapter;
use Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\PhraseCollector;
use Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer;

class JsTest extends \Magento\Test\Integrity\Phrase\AbstractTestCase
{
    /**
     * @var \Magento\Tools\I18n\Code\Parser\Adapter\Js
     */
    protected $_parser;

    /** @var \Magento\TestFramework\Utility\Files  */
    protected $_utilityFiles;

    /** @var \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\PhraseCollector */
    protected $_phraseCollector;

    protected function setUp()
    {
        $this->_parser = new \Magento\Tools\I18n\Code\Parser\Adapter\Js();
        $this->_utilityFiles = \Magento\TestFramework\Utility\Files::init();
        $this->_phraseCollector = new \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer()
        );
    }

    public function testGetPhrasesAdminhtml()
    {
        $unregisteredMessages = array();
        $untranslated = array();

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
        $unregisteredMessages = array();
        $untranslated = array();

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

        $result = array();
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
        $jsPhrases = array();
        foreach ($this->_utilityFiles->getJsFilesForArea($area) as $file) {
            $this->_parser->parse($file);
            $jsPhrases = array_merge($jsPhrases, $this->_parser->getPhrases());
        }
        return $jsPhrases;
    }
}
