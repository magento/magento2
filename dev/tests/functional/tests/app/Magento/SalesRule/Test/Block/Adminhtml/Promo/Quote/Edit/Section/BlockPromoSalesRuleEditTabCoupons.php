<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section;

use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Mapper;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Util\ModuleResolver\SequenceSorterInterface;
use Magento\SalesRule\Test\Block\Adminhtml\Promo\Quote\Edit\Section\BlockPromoSalesRuleEditTabCoupons\Grid;
use Magento\Ui\Test\Block\Adminhtml\Section;

/**
 * Sales rule BlockPromoSalesRuleEditTabCoupons section.
 */
class BlockPromoSalesRuleEditTabCoupons extends Section
{
    /**
     * Success message selector.
     *
     * @var string
     */
    protected $successMessage = '[data-ui-id$=message-success]';

    /**
     * Generate button which generates coupons.
     *
     * @var string
     */
    private $generateButtonSelector = './/*[@id="coupons_generate_button"]//button[contains(@class, "generate")]';

    /**
     * Coupon codes grid.
     *
     * @var string
     */
    private $gridSelector = '#couponCodesGrid_table';

    /**
     * BlockPromoSalesRuleEditTabCoupons constructor.
     * @param SimpleElement $element
     * @param BlockFactory $blockFactory
     * @param Mapper $mapper
     * @param BrowserInterface $browser
     * @param SequenceSorterInterface $sequenceSorter
     * @param array $config
     */
    public function __construct(
        SimpleElement $element,
        BlockFactory $blockFactory,
        Mapper $mapper,
        BrowserInterface $browser,
        SequenceSorterInterface $sequenceSorter,
        array $config = []
    ) {
        parent::__construct($element, $blockFactory, $mapper, $browser, $sequenceSorter, $config);
    }

    /**
     * Press generate button to generate coupons.
     *
     * @return void
     */
    public function pressGenerateButton()
    {
        $this->_rootElement->find($this->generateButtonSelector, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get success message from section.
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        $this->waitForElementVisible($this->successMessage);

        return $this->_rootElement->find($this->successMessage)->getText();
    }

    /**
     * Get coupon codes grid.
     *
     * @return \Magento\Mtf\Block\BlockInterface
     */
    public function getCouponGrid()
    {
        $element = $this->_rootElement->find($this->gridSelector);

        return $this->blockFactory->create(Grid::class, ['element' => $element]);
    }
}
