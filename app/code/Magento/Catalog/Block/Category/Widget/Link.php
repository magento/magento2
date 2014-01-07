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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget to display link to the category
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Catalog\Block\Category\Widget;

class Link
    extends \Magento\Catalog\Block\Widget\Link
{
    /**
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Core\Model\Resource\Url\Rewrite $urlRewrite
     * @param \Magento\Catalog\Model\Resource\Category $resourceCategory
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Core\Model\Resource\Url\Rewrite $urlRewrite,
        \Magento\Catalog\Model\Resource\Category $resourceCategory,
        array $data = array()
    ) {
        parent::__construct($context, $urlRewrite, $data);
        $this->_entityResource = $resourceCategory;
    }
}
