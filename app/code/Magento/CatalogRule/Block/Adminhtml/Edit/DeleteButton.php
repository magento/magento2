<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface
{
    /**
     * Key for current catalog rule id in registry
     */
    const CURRENT_CATALOG_RULE_ID = 'current_promo_catalog_rule';

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $register;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     * @param Registry $register
     */
    public function __construct(UrlInterface $urlBuilder, Registry $register)
    {
        $this->urlBuilder = $urlBuilder;
        $this->register = $register;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $rule = $this->register->registry(static::CURRENT_CATALOG_RULE_ID);
        $ruleId = $rule ? $rule->getId() : null;
        if ($ruleId) {
            $data = [
                'label' => __('Delete Rule'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->urlBuilder->getUrl('*/*/delete', ['id' => $ruleId]) . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }
}
