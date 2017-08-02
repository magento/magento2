<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter;

/**
 * Split words
 * @since 2.0.0
 */
class SplitWords implements \Zend_Filter_Interface
{
    /**
     * @var bool
     * @since 2.0.0
     */
    protected $uniqueOnly;

    /**
     * @var int
     * @since 2.0.0
     */
    protected $wordsQty;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $wordSeparatorRegexp;

    /**
     * @param bool $uniqueOnly Unique words only
     * @param int $wordsQty Limit words qty in result
     * @param string $wordSeparatorRegexp
     * @since 2.0.0
     */
    public function __construct($uniqueOnly = true, $wordsQty = 0, $wordSeparatorRegexp = '\s')
    {
        $this->uniqueOnly = $uniqueOnly;
        $this->wordsQty = $wordsQty;
        $this->wordSeparatorRegexp = $wordSeparatorRegexp;
    }

    /**
     * Filter value
     *
     * @param string $str The source string
     * @return array
     * @since 2.0.0
     */
    public function filter($str)
    {
        $result = [];
        $split = preg_split('#' . $this->wordSeparatorRegexp . '#siu', $str, null, PREG_SPLIT_NO_EMPTY);
        foreach ($split as $word) {
            if ($this->uniqueOnly) {
                $result[$word] = $word;
            } else {
                $result[] = $word;
            }
        }
        if ($this->wordsQty && count($result) > $this->wordsQty) {
            $result = array_slice($result, 0, $this->wordsQty);
        }
        return $result;
    }
}
