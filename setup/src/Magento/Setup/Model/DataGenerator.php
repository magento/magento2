<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A custom adapter that allows generating arbitrary descriptions
 */
namespace Magento\Setup\Model;

/**
 * Class \Magento\Setup\Model\DataGenerator
 *
 * @since 2.2.0
 */
class DataGenerator
{
    /**
     * Location for dictionary file.
     *
     * @var string
     * @since 2.2.0
     */
    private $dictionaryFile;

    /**
     * Dictionary data.
     *
     * @var array
     * @since 2.2.0
     */
    private $dictionaryData;

    /**
     * Map of generated values
     *
     * @var array
     * @since 2.2.0
     */
    private $generatedValues;

    /**
     * DataGenerator constructor.
     *
     * @param string $dictionaryFile
     * @since 2.2.0
     */
    public function __construct($dictionaryFile)
    {
        $this->dictionaryFile = $dictionaryFile;
        $this->readData();
        $this->generatedValues = [];
    }

    /**
     * Read data from file.
     *
     * @return void
     * @since 2.2.0
     */
    protected function readData()
    {
        $f = fopen($this->dictionaryFile, 'r');
        while (!feof($f) && is_array($line = fgetcsv($f))) {
            $this->dictionaryData[] = $line[0];
        }
    }

    /**
     * Generate string of random word data.
     *
     * @param int $minAmountOfWords
     * @param int $maxAmountOfWords
     * @param string|null $key
     * @return string
     * @since 2.2.0
     */
    public function generate($minAmountOfWords, $maxAmountOfWords, $key = null)
    {
        $numberOfWords = mt_rand($minAmountOfWords, $maxAmountOfWords);
        $result = '';

        if ($key === null || !array_key_exists($key, $this->generatedValues)) {
            for ($i = 0; $i < $numberOfWords; $i++) {
                $result .= ' ' . $this->dictionaryData[mt_rand(0, count($this->dictionaryData) - 1)];
            }
            $result = trim($result);

            if ($key !== null) {
                $this->generatedValues[$key] = $result;
            }
        } else {
            $result = $this->generatedValues[$key];
        }
        return $result;
    }
}
