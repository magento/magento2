<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\App\Language;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Setup\Module\I18n\Dictionary\Options\ResolverFactory;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Pack\Writer\File\Csv;
use Magento\Framework\Filesystem\Driver\File;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TranslationFilesTest extends TranslationFiles
{
    /**
     * I18n\Context
     *
     * @var \Magento\Setup\Module\I18n\Context
     */
    protected static $context;

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
    public static function defaultLocaleDataProvider()
    {
        $parser = self::prepareParser();

        $optionResolverFactory = new ResolverFactory();
        $optionResolver = $optionResolverFactory->create(BP, true);

        $parser->parse($optionResolver->getOptions());

        $defaultLocale = [];
        foreach ($parser->getPhrases() as $key => $phrase) {
            if (!$phrase->getContextType() || !$phrase->getContextValue()) {
                throw new \RuntimeException(sprintf('Missed context in row #%d.', $key + 1));
            }
            foreach ($phrase->getContextValue() as $context) {
                $phraseText = self::eliminateSpecialChars($phrase->getPhrase());
                $phraseTranslation = self::eliminateSpecialChars($phrase->getTranslation());
                $file = self::buildFilePath($phrase, $context);
                $defaultLocale[$file]['file'] = $file;
                $defaultLocale[$file]['phrases'][$phraseText] = $phraseTranslation;
            }
        }
        return $defaultLocale;
    }

    /**
     * @param \Magento\Setup\Module\I18n\Dictionary\Phrase $phrase
     * @param array $context
     * @return string
     */
    protected static function buildFilePath($phrase, $context)
    {
        $path = self::getContext()->buildPathToLocaleDirectoryByContext($phrase->getContextType(), $context);
        return $path . Locale::DEFAULT_SYSTEM_LOCALE . '.' . Csv::FILE_EXTENSION;
    }

    /**
     * @return \Magento\Setup\Module\I18n\Context
     */
    protected static function getContext()
    {
        if (self::$context === null) {
            self::$context = new \Magento\Setup\Module\I18n\Context(new ComponentRegistrar());
        }
        return self::$context;
    }

    /**
     * @return \Magento\Setup\Module\I18n\Parser\Contextual
     */
    protected static function prepareParser()
    {
        $filesCollector = new \Magento\Setup\Module\I18n\FilesCollector();

        $phraseCollector = new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer\PhraseCollector(
            new \Magento\Setup\Module\I18n\Parser\Adapter\Php\Tokenizer()
        );
        $fileSystem = new File;
        $adapters = [
            'php' => new \Magento\Setup\Module\I18n\Parser\Adapter\Php($phraseCollector),
            'js' =>  new \Magento\Setup\Module\I18n\Parser\Adapter\Js($fileSystem),
            'xml' => new \Magento\Setup\Module\I18n\Parser\Adapter\Xml(),
            'html' => new \Magento\Setup\Module\I18n\Parser\Adapter\Html(),
        ];

        $parserContextual = new \Magento\Setup\Module\I18n\Parser\Contextual(
            $filesCollector,
            new \Magento\Setup\Module\I18n\Factory(),
            new \Magento\Setup\Module\I18n\Context(new ComponentRegistrar())
        );
        foreach ($adapters as $type => $adapter) {
            $parserContextual->addAdapter($type, $adapter);
        }

        return $parserContextual;
    }

    /**
     * @param string $text
     * @return string
     */
    protected static function eliminateSpecialChars($text)
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

        $failures = [];
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
