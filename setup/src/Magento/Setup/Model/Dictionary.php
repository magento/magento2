<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Provide random word from dictionary
 */
class Dictionary
{
    /**
     * @var string
     */
    private $dictionaryFilePath;

    /**
     * @var \SplFixedArray
     */
    private $dictionary;

    /**
     * @param string $dictionaryFilePath
     * @throws \Magento\Setup\Exception
     */
    public function __construct($dictionaryFilePath)
    {
        $this->dictionaryFilePath = $dictionaryFilePath;
    }

    /**
     * Returns random word from dictionary
     *
     * @return string
     */
    public function getRandWord()
    {
        if ($this->dictionary === null) {
            $this->readDictionary();
        }

        $randIndex = mt_rand(0, count($this->dictionary) - 1);
        return trim($this->dictionary[$randIndex]);
    }

    /**
     * Read dictionary file
     *
     * @return void
     * @throws \Magento\Setup\Exception
     */
    private function readDictionary()
    {
        if (!is_readable($this->dictionaryFilePath)) {
            throw new \Magento\Setup\Exception(
                sprintf('Description file %s not found or is not readable', $this->dictionaryFilePath)
            );
        }

        $rows = file($this->dictionaryFilePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($rows === false) {
            throw new \Magento\Setup\Exception(
                sprintf('Error occurred while reading dictionary file %s', $this->dictionaryFilePath)
            );
        }

        if (empty($rows)) {
            throw new \Magento\Setup\Exception(
                sprintf('Dictionary file %s is empty', $this->dictionaryFilePath)
            );
        }

        $this->dictionary = \SplFixedArray::fromArray($rows);
    }
}
