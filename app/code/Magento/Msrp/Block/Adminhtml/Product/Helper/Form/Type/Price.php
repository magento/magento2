<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Msrp\Block\Adminhtml\Product\Helper\Form\Type;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;

/**
 * Product form MSRP field helper
 * @since 2.0.0
 */
class Price extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @var \Magento\Msrp\Model\Config
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param \Magento\Msrp\Model\Config $config
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        \Magento\Msrp\Model\Config $config,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }
        return parent::toHtml();
    }
}
