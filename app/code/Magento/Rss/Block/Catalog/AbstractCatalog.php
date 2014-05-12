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
namespace Magento\Rss\Block\Catalog;

class AbstractCatalog extends \Magento\Rss\Block\AbstractBlock
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * Stored price block instances
     * @var array
     */
    protected $_priceBlock = array();

    /**
     * Whether to show "As low as" as a link
     * @var bool
     */
    protected $_useLinkForAsLowAs = true;

    /**
     * Default MAP renderer type
     *
     * @var string
     */
    protected $_mapRenderer = 'msrp_rss';

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = array()
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $httpContext, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Get rendered price html
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $displayMinimalPrice
     * @return string
     */
    public function renderPriceHtml(\Magento\Catalog\Model\Product $product, $displayMinimalPrice = false)
    {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => $displayMinimalPrice,
                    'use_link_for_as_low_as' => $this->_useLinkForAsLowAs,
                    'zone'                   => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }
}
