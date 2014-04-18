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
 * @package     Magento_Rss
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
     * @param \Magento\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param array $data
     */
    public function __construct(
        \Magento\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Helper\Data $catalogData,
        array $data = array()
    ) {
        $this->_catalogData = $catalogData;
        parent::__construct($context, $httpContext, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Return Price Block renderer for specified product type
     *
     * @param string $type Catalog Product type
     * @return \Magento\View\Element\Template
     * @throws \RuntimeException
     * @deprecated
     */
    protected function _getPriceBlock($type)
    {
        if (!isset($this->_priceBlock[$type])) {
            /** @var \Magento\View\Element\RendererList $rendererList */
            $rendererList = $this->getRendererListName() ? $this->getLayout()->getBlock(
                $this->getRendererListName()
            ) : $this->getChildBlock(
                'renderer.list'
            );
            if (!$rendererList) {
                throw new \RuntimeException(
                    'Renderer list for block "' . $this->getNameInLayout() . '" is not defined'
                );
            }
            $overriddenTemplates = $this->getOverriddenTemplates() ?: array();
            $template = isset(
                $overriddenTemplates[$type]
            ) ? $overriddenTemplates[$type] : $this->getRendererTemplate();
            $renderer = $rendererList->getRenderer($type, self::DEFAULT_TYPE, $template);
            $this->_priceBlock[$type] = $renderer;
        }
        return $this->_priceBlock[$type];
    }

    /**
     * Returns product price html for RSS feed
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $displayMinimalPrice Display "As low as" etc.
     * @param string $idSuffix Suffix for HTML containers
     * @return string
     * @deprecated see renderPriceHtml
     */
    public function getPriceHtml($product, $displayMinimalPrice = false, $idSuffix = '')
    {
        $typeId = $product->getTypeId();
        if ($this->_catalogData->canApplyMsrp($product)) {
            $typeId = $this->_mapRenderer;
        }

        return $this->_getPriceBlock(
            $typeId
        )->setProduct(
            $product
        )->setDisplayMinimalPrice(
            $displayMinimalPrice
        )->setIdSuffix(
            $idSuffix
        )->setUseLinkForAsLowAs(
            $this->_useLinkForAsLowAs
        )->toHtml();
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
        /** @var \Magento\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPriceInterface::PRICE_TYPE_FINAL,
                $product,
                [
                    'display_minimal_price'  => $displayMinimalPrice,
                    'use_link_for_as_low_as' => $this->_useLinkForAsLowAs,
                    'zone'                   => \Magento\Pricing\Render::ZONE_ITEM_LIST
                ]
            );
        }

        return $price;
    }
}
