<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Block\Transparent;

/**
 * Iframe block for register specific params in layout
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Iframe extends \Magento\Framework\View\Element\Template
{
    const REGISTRY_KEY = 'transparent_form_params';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Preparing layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $params = $this->coreRegistry->registry(self::REGISTRY_KEY);
        $this->setParams($params);
        return parent::_prepareLayout();
    }
}
