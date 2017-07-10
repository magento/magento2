<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
            $randWords[] = $words[mt_rand(0, $wordsSize - 1)];
            $count--;
        }

        return $randWords;
    }
}
