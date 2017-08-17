<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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

/**
 * Class \Magento\Eav\Model\Entity\Increment\Alphanum
 *
 */
class Alphanum extends \Magento\Eav\Model\Entity\Increment\AbstractIncrement
{
    /**
     * Get allowed chars
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getAllowedChars()
    {
        return '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    /**
     * Get next id
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextId()
    {
        $lastId = $this->getLastId();

        if (strpos($lastId, $this->getPrefix()) === 0) {
            $lastId = substr($lastId, strlen($this->getPrefix()));
        }

        $lastId = str_pad((string)$lastId, $this->getPadLength(), $this->getPadChar(), STR_PAD_LEFT);

        $nextId = '';
        $bumpNextChar = true;
        $chars = $this->getAllowedChars();
        $lchars = strlen($chars);
        $lid = strlen($lastId) - 1;

        for ($i = $lid; $i >= 0; $i--) {
            $p = strpos($chars, $lastId[$i]);
            if (false === $p) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid character encountered in increment ID: %1', $lastId)
                );
            }
            if ($bumpNextChar) {
                $p++;
                $bumpNextChar = false;
            }
            if ($p === $lchars) {
                $p = 0;
                $bumpNextChar = true;
            }
            $nextId = $chars[$p] . $nextId;
        }

        return $this->format($nextId);
    }
}
