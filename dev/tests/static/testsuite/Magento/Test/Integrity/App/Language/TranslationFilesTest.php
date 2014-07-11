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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity\App\Language;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslationFilesTest extends TranslationFiles
{
    /**
     * Context
     *
     * @var \Magento\Tools\I18n\Code\Context
     */
    protected $context;

    /**
     * Test default locale
     *
     * Check that all translation phrases in code are present in the locale files
     *
     * @param string $file
     * @param array $phrases
     *
     * @dataProvider defaultLocaleDataProvider
     */
    public function testDefaultLocale($file, $phrases)
    {
        $this->markTestSkipped('MAGETWO-26083');
        $failures = $this->comparePhrase($phrases, $this->csvParser->getDataPairs($file));
        $this->assertEmpty(
            $failures,
            $this->printMessage([$file => $failures])
        );
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function defaultLocaleDataProvider()
    {
        $parser = $this->prepareParser();

        $optionResolverFactory = new \Magento\Tools\I18n\Code\Dictionary\Options\ResolverFactory();
        $optionResolver = $optionResolverFactory->create(
            \Magento\TestFramework\Utility\Files::init()->getPathToSource(),
            true
        );

        $parser->parse($optionResolver->getOptions());

        $defaultLocale = array();
        foreach ($parser->getPhrases() as $key => $phrase) {
            if (!$phrase->getContextType() || !$phrase->getContextValue()) {
                throw new \RuntimeException(sprintf('Missed context in row #%d.', $key + 1));
            }
            foreach ($phrase->getContextValue() as $context) {
                $phraseText = $this->eliminateSpecialChars($phrase->getPhrase());
                $phraseTranslation = $this->eliminateSpecialChars($phrase->getTranslation());
                $file = $this->buildFilePath($phrase, $context);
                $defaultLocale[$file]['file'] = $file;
                $defaultLocale[$file]['phrases'][$phraseText] = $phraseTranslation;
            }
        }
        return $defaultLocale;
    }

    /**
     * @param \Magento\Tools\I18n\Code\Dictionary\Phrase $phrase
     * @param array $context
     * @return string
     */
    protected function buildFilePath($phrase, $context)
    {
        $path = $this->getContext()->buildPathToLocaleDirectoryByContext($phrase->getContextType(), $context);
        return \Magento\TestFramework\Utility\Files::init()->getPathToSource() . '/'
        . $path . \Magento\Tools\I18n\Code\Locale::DEFAULT_SYSTEM_LOCALE
        . '.' . \Magento\Tools\I18n\Code\Pack\Writer\File\Csv::FILE_EXTENSION;
    }

    /**
     * @return \Magento\Tools\I18n\Code\Context
     */
    protected function getContext()
    {
        if ($this->context === null) {
            $this->context = new \Magento\Tools\I18n\Code\Context();
        }
        return $this->context;
    }

    /**
     * @return \Magento\Tools\I18n\Code\Parser\Contextual
     */
    protected function prepareParser()
    {
        $filesCollector = new \Magento\Tools\I18n\Code\FilesCollector();

        $phraseCollector = new \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Tools\I18n\Code\Parser\Adapter\Php\Tokenizer()
        );
        $adapters = array(
            'php' => new \Magento\Tools\I18n\Code\Parser\Adapter\Php($phraseCollector),
            'js' =>  new \Magento\Tools\I18n\Code\Parser\Adapter\Js(),
            'xml' => new \Magento\Tools\I18n\Code\Parser\Adapter\Xml()
        );

        $parserContextual = new \Magento\Tools\I18n\Code\Parser\Contextual(
            $filesCollector,
            new \Magento\Tools\I18n\Code\Factory(),
            new \Magento\Tools\I18n\Code\Context()
        );
        foreach ($adapters as $type => $adapter) {
            $parserContextual->addAdapter($type, $adapter);
        }

        return $parserContextual;
    }

    /**
     * @param string $text
     * @return mixed
     */
    protected function eliminateSpecialChars($text)
    {
        return preg_replace(['/\\\\\'/', '/\\\\\\\\/'], ['\'', '\\'], $text);
    }

    /**
     * Test placeholders in translations.
     * Compares count numeric placeholders in keys and translates.
     *
     * @param string $placePath
     * @dataProvider getLocalePlacePath
     */
    public function testPhrasePlaceHolders($placePath)
    {
        $this->markTestSkipped('MAGETWO-26083');
        $files = $this->getCsvFiles($placePath);

        $failures = array();
        foreach ($files as $locale => $file) {
            $fileData = $this->csvParser->getDataPairs($file);
            foreach ($fileData as $key => $translate) {
                preg_match_all('/%(\d+)/', $key, $keyMatches);
                preg_match_all('/%(\d+)/', $translate, $translateMatches);
                if (count(array_unique($keyMatches[1])) != count(array_unique($translateMatches[1]))) {
                    $failures[$locale][$key][] = $translate;
                }
            }
        }
        $this->assertEmpty(
            $failures,
            $this->printMessage(
                $failures,
                'Found discrepancy between keys and translations in count of numeric placeholders'
            )
        );
    }
}
