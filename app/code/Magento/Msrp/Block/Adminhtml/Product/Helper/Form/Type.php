<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form MSRP field helper
 * @since 2.0.0
 */
class Type extends \Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Price
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Tax\Helper\Data $taxData
     * @param \Magento\Msrp\Model\Config $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Msrp\Model\Config $config,
        array $data = []
    ) {
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $storeManager,
            $localeCurrency,
            $taxData,
            $data
        );
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toHtml()
    {
        if ($this->config->isEnabled()) {
            return parent::toHtml();
        }
        return '';
    }
}
