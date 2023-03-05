<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Wishlist\Block\Customer;

use Magento\Catalog\Block\Product\Context as ProductContext;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Wishlist\Block\AbstractBlock;
use Magento\Wishlist\Model\Item;
use Magento\Wishlist\Model\ResourceModel\Item\Collection;

/**
 * Wishlist block customer items.
 *
 * @api
 * @since 100.0.2
 */
class Wishlist extends AbstractBlock
{
    /**
     * List of product options rendering configurations by product type
     *
     * @var array
     */
    protected $_optionsCfg = [];

    /**
     * @var ConfigurationPool
     */
    protected $_helperPool;

    /**
     * @var  Collection
     * @since 101.1.1
     */
    protected $_collection;

    /**
     * @param ProductContext $context
     * @param Context $httpContext
     * @param ConfigurationPool $helperPool
     * @param CurrentCustomer $currentCustomer
     * @param PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        ProductContext $context,
        Context $httpContext,
        ConfigurationPool $helperPool,
        protected readonly CurrentCustomer $currentCustomer,
        protected readonly PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $httpContext,
            $data
        );
        $this->_helperPool = $helperPool;
    }

    /**
     * Add wishlist conditions to collection
     *
     * @param  Collection $collection
     * @return $this
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Paginate Wishlist Product Items collection
     *
     * @return void
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    private function paginateCollection()
    {
        $page = $this->getRequest()->getParam("p", 1);
        $limit = $this->getRequest()->getParam("limit", 10);
        $this->_collection
            ->setPageSize($limit)
            ->setCurPage($page);
    }

    /**
     * Retrieve Wishlist Product Items collection
     *
     * @return Collection
     * @since 101.1.1
     */
    public function getWishlistItems()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_createWishlistItemCollection();
            $this->_prepareCollection($this->_collection);
            $this->paginateCollection();
        }
        return $this->_collection;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Wish List'));
        $this->getChildBlock('wishlist_item_pager')
            ->setUseContainer(
                true
            )->setShowAmounts(
                true
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )
            ->setCollection($this->getWishlistItems());
        return $this;
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
     */
    public function setOptionsRenderCfgs($optionCfg)
    {
        $this->_optionsCfg = $optionCfg;
        return $this;
    }

    /**
     * Returns all options render configurations
     *
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
     * @param Item $item
     * @return string
     */
    public function getDetailsHtml(Item $item)
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
     * @param Item $item
     * @return float
     */
    public function getAddToCartQty(Item $item)
    {
        $qty = $this->getQty($item);
        return $qty ? $qty : 1;
    }

    /**
     * Get add all to cart params for POST request
     *
     * @return string
     */
    public function getAddAllToCartParams()
    {
        return $this->postDataHelper->getPostData(
            $this->getUrl('wishlist/index/allcart'),
            ['wishlist_id' => $this->getWishlistInstance()->getId()]
        );
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->currentCustomer->getCustomerId()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
