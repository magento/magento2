<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Pack\Writer\File;

use Magento\Setup\Module\I18n\Context;
use Magento\Setup\Module\I18n\Dictionary;
use Magento\Setup\Module\I18n\Factory;
use Magento\Setup\Module\I18n\Locale;
use Magento\Setup\Module\I18n\Pack\WriterInterface;

/**
 * Abstract pack writer
 */
abstract class AbstractFile implements WriterInterface
{
    /**
     * Context
     *
     * @var \Magento\Setup\Module\I18n\Context
     */
    protected $_context;

    /**
     * Dictionary loader. This object is need for read dictionary for merge mode
     *
     * @var \Magento\Setup\Module\I18n\Dictionary\Loader\FileInterface
     */
    protected $_dictionaryLoader;

    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    protected $_factory;

    /**
     * Locale
     *
     * @var \Magento\Setup\Module\I18n\Locale
     */
    protected $_locale;

    /**
     * Save mode. One of const of WriterInterface::MODE_
     *
     * @var string
     */
    protected $_mode;

    /**
     * Writer construct
     *
     * @param Context $context
     * @param Dictionary\Loader\FileInterface $dictionaryLoader
     * @param Factory $factory
     */
    public function __construct(Context $context, Dictionary\Loader\FileInterface $dictionaryLoader, Factory $factory)
    {
        $this->_context = $context;
        $this->_dictionaryLoader = $dictionaryLoader;
        $this->_factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Dictionary $dictionary, $packPath, Locale $locale, $mode = self::MODE_REPLACE)
    {
        $this->writeDictionary($dictionary, $locale, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function writeDictionary(Dictionary $dictionary, Locale $locale, $mode = self::MODE_REPLACE)
    {
        $this->_locale = $locale;
        $this->_mode = $mode;

        foreach ($this->_buildPackFilesData($dictionary) as $file => $phrases) {
            $this->_createDirectoryIfNotExist(dirname($file));
            $this->_writeFile($file, $phrases);
        }
    }

    /**
     * Create one pack file. Template method
     *
     * @param string $file
     * @param array $phrases
     * @return void
     * @throws \RuntimeException
     */
    abstract public function _writeFile($file, $phrases);

    /**
     * Build pack files data
     *
     * @param Dictionary $dictionary
     * @return array
     * @throws \RuntimeException
     */
    protected function _buildPackFilesData(Dictionary $dictionary)
    {
        $files = [];
        foreach ($dictionary->getPhrases() as $key => $phrase) {
            if (!$phrase->getContextType() || !$phrase->getContextValue()) {
                throw new \RuntimeException(
                    sprintf('Missed context in row #%d.', $key + 1)
                    . "\n"
                    . 'Each row has to consist of 4 columns: original phrase, translation, context type, context value'
                );
            }
            foreach ($phrase->getContextValue() as $context) {
                try {
                    $path = $this->_context->buildPathToLocaleDirectoryByContext($phrase->getContextType(), $context);
                } catch (\InvalidArgumentException $e) {
                    throw new \InvalidArgumentException($e->getMessage() . ' Row #' . ($key + 1) . '.');
                }
                $filename = $path . $this->_locale . '.' . $this->_getFileExtension();
                $files[$filename][$phrase->getPhrase()] = $phrase;
            }
        }
        return $files;
    }

    /**
     * Get file extension
     *
     * @return string
     */
    abstract protected function _getFileExtension();

    /**
     * Create directory if not exists
     *
     * @param string $destinationPath
     * @param int $mode
     * @param bool $recursive Allows the creation of nested directories specified in the $destinationPath
     * @return void
     */
    protected function _createDirectoryIfNotExist($destinationPath, $mode = 0777, $recursive = true)
    {
        if (!is_dir($destinationPath)) {
            mkdir($destinationPath, $mode, $recursive);
            if ($mode) {
                chmod($destinationPath, $mode);
            }
        }
    }
}
