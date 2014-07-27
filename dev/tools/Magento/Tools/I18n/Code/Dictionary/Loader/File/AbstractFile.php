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
namespace Magento\Tools\I18n\Code\Dictionary\Loader\File;

use Magento\Tools\I18n\Code\Dictionary\Loader\FileInterface;
use Magento\Tools\I18n\Code\Factory;

/**
 *  Abstract dictionary loader from file
 */
abstract class AbstractFile implements FileInterface
{
    /**
     * Domain abstract factory
     *
     * @var \Magento\Tools\I18n\Code\Factory
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
                    array(
                        'phrase' => $data[0],
                        'translation' => $data[1],
                        'context_type' => $data[2],
                        'context_value' => $data[3]
                    )
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
     * @return \Magento\Tools\I18n\Code\Dictionary
     */
    protected function _createDictionary()
    {
        return $this->_factory->createDictionary();
    }

    /**
     * Create phrase
     *
     * @param array $data
     * @return \Magento\Tools\I18n\Code\Dictionary\Phrase
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
