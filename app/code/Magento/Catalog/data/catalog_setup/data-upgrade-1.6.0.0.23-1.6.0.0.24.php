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

/** @var $this \Magento\Catalog\Model\Resource\Setup */

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'category_ids',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Category'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'image',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\BaseImage'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Product::ENTITY,
    'weight',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Weight'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'available_sort_by',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\Available'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'default_sort_by',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Sortby\DefaultSortby'
);

$this->updateAttribute(
    \Magento\Catalog\Model\Category::ENTITY,
    'filter_price_range',
    'frontend_input_renderer',
    'Magento\Catalog\Block\Adminhtml\Category\Helper\Pricestep'
);
