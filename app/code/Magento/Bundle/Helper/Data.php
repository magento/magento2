<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Helper;

/**
 * Bundle helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     * @since 2.0.0
     */
    protected $config;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $config
    ) {
        $this->config = $config;
        parent::__construct($context);
    }

    /**
     * Retrieve array of allowed product types for bundle selection product
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllowedSelectionTypes()
    {
        $configData = $this->config->getType(\Magento\Catalog\Model\Product\Type::TYPE_BUNDLE);

        return isset($configData['allowed_selection_types']) ? $configData['allowed_selection_types'] : [];
    }
}
