<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Writer;

use Magento\Setup\Module\I18n\Dictionary\Phrase;
use Magento\Setup\Module\I18n\Dictionary\WriterInterface;

/**
 * Csv writer
 */
class Csv implements WriterInterface
{
    /**
     * File handler
     *
     * @var resource
     */
    protected $_fileHandler;

    /**
     * Writer construct
     *
     * @param string $outputFilename
     * @throws \InvalidArgumentException
     */
    public function __construct($outputFilename)
    {
        if (false === ($fileHandler = @fopen($outputFilename, 'w'))) {
            throw new \InvalidArgumentException(
                sprintf('Cannot open file for write dictionary: "%s"', $outputFilename)
            );
        }
        $this->_fileHandler = $fileHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function write(Phrase $phrase)
    {
        $fields = [$phrase->getCompiledPhrase(), $phrase->getCompiledTranslation()];
        if (($contextType = $phrase->getContextType()) && ($contextValue = $phrase->getContextValueAsString())) {
            $fields[] = $contextType;
            $fields[] = $contextValue;
        }

        fputcsv($this->_fileHandler, $fields, ',', '"');
    }

    /**
     * Close file handler
     *
     * @return void
     *
     * @deprecated
     */
    public function __destructor()
    {
        fclose($this->_fileHandler);
    }

    /**
     * Destructor for closing file handler
     */
    public function __destruct()
    {
        fclose($this->_fileHandler);
    }
}
