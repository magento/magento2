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

namespace Magento\PageCache\Model\System\Config\Backend;

/**
 * Backend model for processing Public content cache lifetime settings
 *
 * Class Ttl
 */
class Ttl extends \Magento\Framework\App\Config\Value
{
    /**
     * Throw exception if Ttl data is invalid or empty
     *
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if ($value < 0 || !preg_match('/^[0-9]+$/', $value)) {
            throw new \Magento\Framework\Model\Exception(
                __('Ttl value "%1" is not valid. Please use only numbers equal or greater than zero.', $value)
            );
        }
        return $this;
    }
}
