<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Wishlist block customer items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

class Wishlist extends \Magento\Wishlist\Block\AbstractBlock
{
    /**
     * List of product options rendering configurations by product type
     *
     * @var array
     */
    protected $_optionsCfg = [];

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $_helperPool;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $_formKey;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->_formKey = $formKey;
        $this->_helperPool = $helperPool;
        parent::__construct(
            $context,
            $httpContext,
            $productRepository,
            $data
        );
    }

    /**
     * Add wishlist conditions to collection
     *
     * @param  \Magento\Wishlist\Model\Resource\Item\Collection $collection
     * @return $this
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Preparing global layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Wish List'));
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Sets all options render configurations
     *
     * @param null|array $optionCfg
     * @return $this
     *
     * @deprecated after 1.6.2.0
     */
    public function setOptionsRenderCfgs($optionCfg)
    {
        $this->_optionsCfg = $optionCfg;
        return $this;
    }

    /**
     * Returns all options render configurations
     *
     * @deprecated after 1.6.2.0
     * @return array
     */
    public function getOptionsRenderCfgs()
    {
        return $this->_optionsCfg;
    }

    /**
     * Adds config for rendering product type options
     *
     * @param string $productType
     * @param string $helperName
     * @param null|string $template
     * @return $this
     *
     * @deprecated after 1.6.2.0
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = ['helper' => $helperName, 'template' => $template];
        return $this;
    }

    /**
     * Returns html for showing item options
     *
     * @param string $productType
     * @return array|null
     *
     * @deprecated after 1.6.2.0
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return null;
        }
    }

    /**
     * Returns html for showing item options
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return string
     *
     * @deprecated after 1.6.2.0
     */
    public function getDetailsHtml(\Magento\Wishlist\Model\Item $item)
    {
        $cfg = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());
        if (!$cfg) {
            return '';
        }

        $block = $this->getChildBlock('item_options');
        if (!$block) {
            return '';
        }

        if ($cfg['template']) {
            $template = $cfg['template'];
        } else {
            $cfgDefault = $this->getOptionsRenderCfg('default');
            if (!$cfgDefault) {
                return '';
            }
            $template = $cfgDefault['template'];
        }

        $block->setTemplate($template);
        $block->setOptionList($this->_helperPool->get($cfg['helper'])->getOptions($item));
        return $block->toHtml();
    }

    /**
     * Returns qty to show visually to user
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @return float
     *
     * @deprecated after 1.6.2.0
     */
    public function getAddToCartQty(\Magento\Wishlist\Model\Item $item)
    {
        $qty = $this->getQty($item);
        return $qty ? $qty : 1;
    }

    /**
     * Get add all to cart url
     * @return string
     */
    public function getAddAllToCartUrl()
    {
        return $this->getUrl(
            '*/*/allcart',
            ['wishlist_id' => $this->getWishlistInstance()->getId(), 'form_key' => $this->_formKey->getFormKey()]
        );
    }
}
