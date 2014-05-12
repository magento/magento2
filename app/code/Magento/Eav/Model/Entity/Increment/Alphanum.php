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

use Magento\Eav\Exception;

class Alphanum extends \Magento\Eav\Model\Entity\Increment\AbstractIncrement
{
    /**
     * Get allowed chars
     *
     * @return string
     */
    public function getAllowedChars()
    {
        return '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    }

    /**
     * Get next id
     *
     * @return string
     * @throws Exception
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
                throw new \Magento\Eav\Exception(__('Invalid character encountered in increment ID: %1', $lastId));
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
