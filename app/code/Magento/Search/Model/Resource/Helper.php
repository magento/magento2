<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model\Resource;

/**
 * Search Mysql resource helper model
 */
class Helper extends \Magento\Framework\DB\Helper
{
    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param string $modulePrefix
     */
    public function __construct(\Magento\Framework\App\Resource $resource, $modulePrefix = 'Magento_Search')
    {
        parent::__construct($resource, $modulePrefix);
    }

    /**
     * Prepare Terms
     *
     * @param string $str The source string
     * @param int $maxWordLength
     * @return array (0 => words, 1 => terms)
     */
    public function prepareTerms($str, $maxWordLength = 0)
    {
        $boolWords = ['+' => '+', '-' => '-', '|' => '|', '<' => '<', '>' => '>', '~' => '~', '*' => '*'];
        $brackets = ['(' => '(', ')' => ')'];
        $words = [0 => ""];
        $terms = [];
        preg_match_all('/([\(\)]|[\"\'][^"\']*[\"\']|[^\s\"\(\)]*)/uis', $str, $matches);
        $isOpenBracket = 0;
        foreach ($matches[1] as $word) {
            $word = trim($word);
            if (strlen($word)) {
                $word = str_replace('"', '', $word);
                $isBool = in_array(strtoupper($word), $boolWords);
                $isBracket = in_array($word, $brackets);
                if (!$isBool && !$isBracket) {
                    $terms[$word] = $word;
                    $word = '"' . $word . '"';
                    $words[] = $word;
                } elseif ($isBracket) {
                    if ($word == '(') {
                        $isOpenBracket++;
                    } else {
                        $isOpenBracket--;
                    }
                    $words[] = $word;
                } elseif ($isBool) {
                    $words[] = $word;
                }
            }
        }
        if ($isOpenBracket > 0) {
            $words[] = sprintf("%')" . $isOpenBracket . "s", '');
        } elseif ($isOpenBracket < 0) {
            $words[0] = sprintf("%'(" . $isOpenBracket . "s", '');
        }
        if ($maxWordLength && count($terms) > $maxWordLength) {
            $terms = array_slice($terms, 0, $maxWordLength);
        }
        $result = [$words, $terms];
        return $result;
    }

    /**
     * Use sql compatible with Full Text indexes
     *
     * @param string $table The table to insert data into.
     * @param array $data Column-value pairs or array of column-value pairs.
     * @param array $fields update fields pairs or values
     * @return int The number of affected rows.
     */
    public function insertOnDuplicate($table, array $data, array $fields = [])
    {
        return $this->_getWriteAdapter()->insertOnDuplicate($table, $data, $fields);
    }
}
