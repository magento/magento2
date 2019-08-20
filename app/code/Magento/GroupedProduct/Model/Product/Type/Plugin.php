<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product\Type;

use Magento\Framework\Module\ModuleManagerInterface;

/**
 * Plugin.
 */
class Plugin
{
    /**
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $moduleManager;

    /**
     * @param ModuleManagerInterface $moduleManager
     */
    public function __construct(ModuleManagerInterface $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * Remove grouped product from list of visible product types
     *
     * @param \Magento\Catalog\Model\Product\Type $subject
     * @param array $result
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetOptionArray(\Magento\Catalog\Model\Product\Type $subject, array $result)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_GroupedProduct')) {
            unset($result[Grouped::TYPE_CODE]);
        }
        return $result;
    }
}
