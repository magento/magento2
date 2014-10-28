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
 * Image include policy into sitemap file
 *
 */
namespace Magento\Sitemap\Model\Source\Product\Image;

class IncludeImage implements \Magento\Framework\Option\ArrayInterface
{
    /**#@+
     * Add Images into Sitemap possible values
     */
    const INCLUDE_NONE = 'none';

    const INCLUDE_BASE = 'base';

    const INCLUDE_ALL = 'all';

    /**#@-*/

    /**
     * Retrieve options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            self::INCLUDE_NONE => __('None'),
            self::INCLUDE_BASE => __('Base Only'),
            self::INCLUDE_ALL => __('All')
        );
    }
}
