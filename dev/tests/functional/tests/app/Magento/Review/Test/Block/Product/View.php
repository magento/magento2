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
 * @api
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Test\Block\Product;

use Mtf\Block\Block;
use Mtf\Client\Element;

/**
 * Class View
 * Reviews frontend block
 */
class View extends Block
{
    /**
     * Review item selector
     *
     * @var string
     */
    protected $itemSelector = '.review-items .review-item';

    /**
     * Selectors mapping
     *
     * @var array
     */
    protected $selectorsMapping = [
        'nickname' => '.review-author .review-details-value',
        'title' => '.review-title',
        'detail' => '.review-content',
    ];

    /**
     * Is visible review item
     *
     * @return bool
     */
    public function isVisibleReviewItem()
    {
        return $this->_rootElement->find($this->itemSelector)->isVisible();
    }

    /**
     * Get field value for review on product view page
     *
     * @param string $field
     * @return string|null
     */
    public function getFieldValue($field)
    {
        if (isset($this->selectorsMapping[$field])) {
            return $this->_rootElement->find($this->selectorsMapping[$field])->getText();
        }

        return null;
    }
}
