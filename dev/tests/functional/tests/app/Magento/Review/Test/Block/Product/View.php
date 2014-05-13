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
 * Reviews frontend block
 *
 */
class View extends Block
{
    /**
     * Review item selector
     *
     * @var string
     */
    protected $itemSelector = '.reviews.items .item.review';

    /**
     * Nickname selector
     *
     * @var string
     */
    protected $nicknameSelector = '.nickname';

    /**
     * Title selector
     *
     * @var string
     */
    protected $titleSelector = '.title';

    /**
     * Detail selector
     *
     * @var string
     */
    protected $detailSelector = '.content';

    /**
     * Selectors mapping
     *
     * @var array
     */
    protected $selectorsMapping;

    /**
     * {@inheritdoc}
     */
    protected function _init()
    {
        parent::_init();
        $this->selectorsMapping = array(
            'nickname' => $this->nicknameSelector,
            'title' => $this->titleSelector,
            'detail' => $this->detailSelector,
        );
    }

    /**
     * Get first review item
     *
     * @return Element
     */
    public function getFirstReview()
    {
        return $this->_rootElement->find($this->itemSelector);
    }

    /**
     * Get selector field for review on product view page
     *
     * @param string $field
     * @return string
     * @throws \Exception
     */
    public function getFieldSelector($field)
    {
        if (!isset($this->selectorsMapping[$field])) {
            throw new \Exception(sprintf('Selector of field "%s" is not defined', $field));
        }
        return $this->selectorsMapping[$field];
    }
}
