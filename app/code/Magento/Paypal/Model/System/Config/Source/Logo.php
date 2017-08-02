<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\System\Config\Source;

/**
 * Source model for available logo types
 * @since 2.0.0
 */
class Logo implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Paypal\Model\ConfigFactory
     * @since 2.0.0
     */
    protected $_configFactory;

    /**
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Paypal\Model\ConfigFactory $configFactory)
    {
        $this->_configFactory = $configFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $result = ['' => __('No Logo')];
        $result += $this->_configFactory->create()->getAdditionalOptionsLogoTypes();
        return $result;
    }
}
