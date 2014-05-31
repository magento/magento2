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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Filter;

/**
 * Split words
 */
class SplitWords implements \Zend_Filter_Interface
{
    /**
     * @var bool
     */
    protected $uniqueOnly;

    /**
     * @var int
     */
    protected $wordsQty;

    /**
     * @var string
     */
    protected $wordSeparatorRegexp;

    /**
     * @param bool $uniqueOnly Unique words only
     * @param int $wordsQty Limit words qty in result
     * @param string $wordSeparatorRegexp
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
     */
    public function filter($str)
    {
        $result = array();
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
