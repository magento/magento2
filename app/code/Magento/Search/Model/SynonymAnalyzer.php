<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);
namespace Magento\Search\Model;

use Magento\Search\Api\SynonymAnalyzerInterface;

/**
 * SynonymAnalyzer responsible for search of synonyms matching a word or a phrase.
 */
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
        $result = [];

        if (empty(trim($phrase))) {
            return $result;
        }

        $synonymGroups = $this->getSynonymGroupsByPhrase($phrase);

        // Replace multiple spaces in a row with the only one space
        $phrase = preg_replace("/ {2,}/", " ", $phrase);

        // Go through every returned record looking for presence of the actual phrase. If there were no matching
        // records found in DB then create a new entry for it in the returned array
        $words = explode(' ', $phrase);

        foreach ($words as $offset => $word) {
            $synonyms = [$word];

            if ($synonymGroups) {
                $pattern = $this->getSearchPattern(array_slice($words, $offset));
                $position = $this->findInArray($pattern, $synonymGroups);
                if ($position !== null) {
                    $synonyms = explode(',', $synonymGroups[$position]);
                }
            }

            $result[] = $synonyms;
        }

        return $result;
    }

    /**
     * Helper method to find the matching of $pattern to $synonymGroupsToExamine.
     * If matches, the particular array index is returned.
     * Otherwise false will be returned.
     *
     * @param string $pattern
     * @param array $synonymGroupsToExamine
     * @return int|null
     */
    private function findInArray(string $pattern, array $synonymGroupsToExamine)
    {
        $position = 0;
        foreach ($synonymGroupsToExamine as $synonymGroup) {
            $matchingResultCode = preg_match($pattern, $synonymGroup);
            if ($matchingResultCode === 1) {
                return $position;
            }
            $position++;
        }
        return null;
    }

    /**
     * Returns a regular expression to search for synonyms of the phrase represented as the list of words.
     *
     * Returned pattern contains expression to search for a part of the phrase from the beginning.
     *
     * For example, in the phrase "Elizabeth is the English queen" with subset from the very first word,
     * the method will build an expression which looking for synonyms for all these patterns:
     * - Elizabeth is the English queen
     * - Elizabeth is the English
     * - Elizabeth is the
     * - Elizabeth is
     * - Elizabeth
     *
     * For the same phrase on the second iteration with the first word "is" it will match for these synonyms:
     * - is the English queen
     * - is the English
     * - is the
     * - is
     *
     * The pattern looking for exact match and will not find these phrases as synonyms:
     * - Is there anybody in the room?
     * - Is the English is most popular language?
     * - Is the English queen Elizabeth?
     *
     * Take into account that returned pattern expects that data will be represented as comma-separated value.
     *
     * @param array $words
     * @return string
     */
    private function getSearchPattern(array $words): string
    {
        $patterns = [];
        for ($lastItem = count($words); $lastItem > 0; $lastItem--) {
            $phrase = implode("\s+", array_slice($words, 0, $lastItem));
            $patterns[] = '^' . $phrase . ',';
            $patterns[] = ',' . $phrase . ',';
            $patterns[] = ',' . $phrase . '$';
        }

        $pattern = '/' . implode('|', $patterns) . '/i';
        return $pattern;
    }

    /**
     * Get all synonym groups for the phrase
     *
     * Returns an array of synonyms which are represented as comma-separated value for each item in the list
     *
     * @param string $phrase
     * @return string[]
     */
    private function getSynonymGroupsByPhrase(string $phrase): array
    {
        $result = [];

        $synonymGroups = $this->synReaderModel->loadByPhrase($phrase)->getData();
        foreach ($synonymGroups as $row) {
            $result[] = $row['synonyms'];
        }
        return $result;
    }
}
