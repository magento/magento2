<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Loader\File;

use Magento\Setup\Module\I18n\Dictionary\Loader\FileInterface;
use Magento\Setup\Module\I18n\Factory;

/**
 *  Abstract dictionary loader from file
 */
abstract class AbstractFile implements FileInterface
{
    /**
     * Domain abstract factory
     *
     * @var \Magento\Setup\Module\I18n\Factory
     */
    protected $_factory;

    /**
     * File handler
     *
     * @var resource
     */
    protected $_fileHandler;

    /**
     * Current row position
     *
     * @var int
     */
    protected $_position;

    /**
     * Loader construct
     *
     * @param Factory $factory
     */
    public function __construct(Factory $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file)
    {
        $this->_openFile($file);
        $dictionary = $this->_createDictionary();

        $this->_position = 0;
        while ($data = $this->_readFile()) {
            $this->_position++;
            $data = array_pad($data, 4, null);
            $dictionary->addPhrase(
                $this->_createPhrase(
                    [
                        'phrase' => $data[0],
                        'translation' => $data[1],
                        'context_type' => $data[2],
                        'context_value' => $data[3],
                    ]
                )
            );
        }
        $this->_closeFile();

        return $dictionary;
    }

    /**
     * Init file handler
     *
     * @param string $file
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function _openFile($file)
    {
        if (false === ($this->_fileHandler = @fopen($file, 'r'))) {
            throw new \InvalidArgumentException(sprintf('Cannot open dictionary file: "%s".', $file));
        }
    }

    /**
     * Read file. Template method
     *
     * @return array
     */
    abstract protected function _readFile();

    /**
     * Close file handler
     *
     * @return void
     */
    protected function _closeFile()
    {
        fclose($this->_fileHandler);
    }

    /**
     * Create dictionary
     *
     * @return \Magento\Setup\Module\I18n\Dictionary
     */
    protected function _createDictionary()
    {
        return $this->_factory->createDictionary();
    }

    /**
     * Create phrase
     *
     * @param array $data
     * @return \Magento\Setup\Module\I18n\Dictionary\Phrase
     * @throws \RuntimeException
     */
    protected function _createPhrase($data)
    {
        try {
            return $this->_factory->createPhrase($data);
        } catch (\DomainException $e) {
            throw new \RuntimeException(
                sprintf('Invalid row #%d: "%s".', $this->_position, $e->getMessage())
                . "\n"
                . 'Each row has to consist of 3 columns: original phrase, translation, context'
            );
        }
    }
}
