<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Search\Api\SynonymAnalyzerInterface;

class SynonymAnalyzer implements SynonymAnalyzerInterface
{
    /**
     * @var SynonymReader $synReaderModel
     */
    protected $synReaderModel;

    /**
     * Constructor
     *
     * @param SynonymReader $synReader
     */
    public function __construct(SynonymReader $synReader)
    {
        $this->synReaderModel = $synReader;
    }

    /**
     * Returns an array of arrays consisting of the synonyms found for each word in the input phrase
     *
     * For phrase: "Elizabeth is the English queen" correct output is an array of arrays containing synonyms for each
     * word in the phrase:
     *
     * [
     *   0 => [ 0 => "elizabeth" ],
     *   1 => [ 0 => "is" ],
     *   2 => [ 0 => "the" ],
     *   3 => [ 0 => "british", 1 => "english" ],
     *   4 => [ 0 => "queen", 1 => "monarch" ]
     * ]
     * @param string $phrase
     * @return array
     */
    public function getSynonymsForPhrase($phrase)
    {
        $synGroups = [];

        if (empty($phrase)) {
            return $synGroups;
        }

        $rows = $this->synReaderModel->loadByPhrase($phrase)->getData();
        $synonyms = [];
        foreach ($rows as $row) {
            $synonyms [] = $row['synonyms'];
        }

        // Go through every returned record looking for presence of the actual phrase. If there were no matching
        // records found in DB then create a new entry for it in the returned array
        $words = explode(' ', $phrase);
        foreach ($words as $w) {
            $position = $this->findInArray($w, $synonyms);
            if ($position !== false) {
                $synGroups[] = explode(',', $synonyms[$position]);
            } else {
                // No synonyms were found. Return the original word in this position
                $synGroups[] = [$w];
            }
        }
        return $synGroups;
    }

    /**
     * Helper method to find the presence of $word in $wordsArray. If found, the particular array index is returned.
     * Otherwise false will be returned.
     *
     * @param string $word
     * @param $array $wordsArray
     * @return boolean | int
     */
    private function findInArray($word, $wordsArray)
    {
        if (empty($wordsArray)) {
            return false;
        }
        $position = 0;
        foreach ($wordsArray as $wordsLine) {
            $pattern = '/^' . $word . ',|,' . $word . ',|,' . $word . '$/';
            $rv = preg_match($pattern, $wordsLine);
            if ($rv != 0) {
                return $position;
            }
            $position++;
        }
        return false;
    }
}
