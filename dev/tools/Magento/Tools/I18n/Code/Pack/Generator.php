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
namespace Magento\Tools\I18n\Code\Pack;

use Magento\Tools\I18n\Code\Dictionary;
use Magento\Tools\I18n\Code\Pack;
use Magento\Tools\I18n\Code\Factory;

/**
 * Pack generator
 */
class Generator
{
    /**
     * Dictionary loader
     *
     * @var \Magento\Tools\I18n\Code\Dictionary\Loader\FileInterface
     */
    protected $dictionaryLoader;

    /**
     * Pack writer
     *
     * @var \Magento\Tools\I18n\Code\Pack\WriterInterface
     */
    protected $packWriter;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
     */
    protected $factory;

    /**
     * Loader construct
     *
     * @param \Magento\Tools\I18n\Code\Dictionary\Loader\FileInterface $dictionaryLoader
     * @param \Magento\Tools\I18n\Code\Pack\WriterInterface $packWriter
     * @param \Magento\Tools\I18n\Code\Factory $factory
     */
    public function __construct(
        Dictionary\Loader\FileInterface $dictionaryLoader,
        Pack\WriterInterface $packWriter,
        Factory $factory
    ) {
        $this->dictionaryLoader = $dictionaryLoader;
        $this->packWriter = $packWriter;
        $this->factory = $factory;
    }

    /**
     * Generate language pack
     *
     * @param string $dictionaryPath
     * @param string $packPath
     * @param string $locale
     * @param string $mode One of const of WriterInterface::MODE_
     * @param bool $allowDuplicates
     * @return void
     * @throws \RuntimeException
     */
    public function generate(
        $dictionaryPath,
        $packPath,
        $locale,
        $mode = WriterInterface::MODE_REPLACE,
        $allowDuplicates = false
    ) {
        $locale = $this->factory->createLocale($locale);
        $dictionary = $this->dictionaryLoader->load($dictionaryPath);

        if (!count($dictionary->getPhrases())) {
            throw new \UnexpectedValueException('No phrases have been found by the specified path.');
        }

        if (!$allowDuplicates && ($duplicates = $dictionary->getDuplicates())) {
            throw new \RuntimeException(
                "Duplicated translation is found, but it is not allowed.\n"
                . $this->createDuplicatesPhrasesError($duplicates)
            );
        }

        $this->packWriter->write($dictionary, $packPath, $locale, $mode);
    }

    /**
     * Get duplicates error
     *
     * @param array $duplicates
     * @return string
     */
    protected function createDuplicatesPhrasesError($duplicates)
    {
        $error = '';
        foreach ($duplicates as $phrases) {
            /** @var \Magento\Tools\I18n\Code\Dictionary\Phrase $phrase */
            $phrase = $phrases[0];
            $error .= sprintf(
                "The phrase \"%s\" is translated differently in %d places.\n",
                $phrase->getPhrase(),
                count($phrases)
            );
        }
        return $error;
    }
}
