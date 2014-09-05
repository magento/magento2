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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model\Coupon;

class Codegenerator extends \Magento\Framework\Object implements \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
{
    /**
     * The minimum length of the default
     */
    const DEFAULT_LENGTH_MIN = 16;

    /**
     * The maximal length of the default
     */
    const DEFAULT_LENGTH_MAX = 32;

    /**
     * Collection of the default symbols
     */
    const SYMBOLS_COLLECTION = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    /**
     * Delimiter default
     */
    const DEFAULT_DELIMITER = '-';

    /**
     * Retrieve generated code
     *
     * @return string
     */
    public function generateCode()
    {
        $alphabet = $this->getAlphabet() ? $this->getAlphabet() : static::SYMBOLS_COLLECTION;
        $length = $this->getActualLength();
        $code = '';
        for ($i = 0, $indexMax = strlen($alphabet) - 1; $i < $length; ++$i) {
            $code .= substr($alphabet, mt_rand(0, $indexMax), 1);
        }

        return $code;
    }

    /**
     * Getting actual code length
     *
     * @return int
     */
    protected function getActualLength()
    {
        $lengthMin = $this->getLengthMin() ? $this->getLengthMin() : static::DEFAULT_LENGTH_MIN;
        $lengthMax = $this->getLengthMax() ? $this->getLengthMax() : static::DEFAULT_LENGTH_MAX;

        return $this->getLength() ? $this->getLength() : mt_rand($lengthMin, $lengthMax);
    }

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->hasData('delimiter') ? $this->getData('delimiter') : static::DEFAULT_DELIMITER;
    }
}
