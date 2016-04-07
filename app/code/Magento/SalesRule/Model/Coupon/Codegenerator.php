<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Coupon;

class Codegenerator extends \Magento\Framework\DataObject implements CodegeneratorInterface
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
