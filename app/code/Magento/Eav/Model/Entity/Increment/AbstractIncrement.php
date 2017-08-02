<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Increment;

/**
 * Properties:
 * - prefix
 * - pad_length
 * - pad_char
 * - last_id
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractIncrement extends \Magento\Framework\DataObject implements
    \Magento\Eav\Model\Entity\Increment\IncrementInterface
{
    /**
     * Get pad length
     *
     * @return int
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function frontendFormat($id)
    {
        return $id;
    }
}
