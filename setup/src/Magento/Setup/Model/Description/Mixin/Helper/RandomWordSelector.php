<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Setup\Model\Description\Mixin\Helper;

/**
 * Return random words from source
 */
class RandomWordSelector
{
    /**
     * Return $count random words from $source
     *
     * @param string $source
     * @param int $count
     * @return array
     */
    public function getRandomWords($source, $count)
    {
        $words = str_word_count($source, 1);
        if (empty($words)) {
            return [];
        }

        $randWords = [];
        $wordsSize = count($words);
        while ($count) {
            // mt_rand() here is not for cryptographic use.
            // phpcs:ignore Magento2.Security.InsecureFunction
            $randWords[] = $words[mt_rand(0, $wordsSize - 1)];
            $count--;
        }

        return $randWords;
    }
}
