<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Enter description here...
 *
 * Properties:
 * - prefix
 * - pad_length
 * - pad_char
 * - last_id
 */
namespace Magento\Eav\Model\Entity\Increment;

abstract class AbstractIncrement extends \Magento\Framework\Object implements
    \Magento\Eav\Model\Entity\Increment\IncrementInterface
{
    /**
     * Get pad length
     *
     * @return int
     */
    public function getPadLength()
    {
        $padLength = $this->getData('pad_length');
        if (empty($padLength)) {
            $padLength = 8;
        }
        return $padLength;
    }

    /**
     * Get pad char
     *
     * @return string
     */
    public function getPadChar()
    {
        $padChar = $this->getData('pad_char');
        if (empty($padChar)) {
            $padChar = '0';
        }
        return $padChar;
    }

    /**
     * Pad format
     *
     * @param mixed $id
     * @return string
     */
    public function format($id)
    {
        $result = $this->getPrefix();
        $result .= str_pad((string)$id, $this->getPadLength(), $this->getPadChar(), STR_PAD_LEFT);
        return $result;
    }

    /**
     * Frontend format
     *
     * @param mixed $id
     * @return mixed
     */
    public function frontendFormat($id)
    {
        return $id;
    }
}
