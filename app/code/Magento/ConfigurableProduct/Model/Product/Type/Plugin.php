<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type;

use Magento\Framework\Module\Manager;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\Type\Plugin
 *
 * @since 2.0.0
 */
class Plugin
{
    /**
     * @var \Magento\Framework\Module\Manager
     * @since 2.0.0
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     * @since 2.0.0
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Remove configurable product type from list of visible product types
     *
     * @param \Magento\Catalog\Model\Product\Type $subject
     * @param array $result
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function afterGetOptionArray(\Magento\Catalog\Model\Product\Type $subject, array $result)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_ConfigurableProduct')) {
            unset($result[Configurable::TYPE_CODE]);
        }
        return $result;
    }
}
