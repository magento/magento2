<?php
/**
 * Plugin for \Magento\Catalog\Model\Category\DataProvider
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Model\Plugin;

use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Sets the default value for Category Design Layout if provided
 */
class SetPageLayoutDefaultValue
{
    private $defaultValue;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string $defaultValue
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        string $defaultValue = ""
    ) {
        $this->defaultValue = $defaultValue;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Sets the default value for Category Design Layout in data provider if provided
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     *
     * @throws NoSuchEntityException
     */
    public function afterGetDefaultMetaData(DataProvider $subject, array $result): array
    {
        $currentCategory = $subject->getCurrentCategory();

        if ($currentCategory && !$currentCategory->getId() && array_key_exists('page_layout', $result)) {
            $defaultAdminValue = $this->scopeConfig->getValue(
                'web/default_layouts/default_category_layout',
                ScopeInterface::SCOPE_STORE,
                $this->storeManager->getStore()->getId()
            );

            $defaultValue = $defaultAdminValue ?: $this->defaultValue;

            $result['page_layout']['default'] = $defaultValue ?: null;
        }

        return $result;
    }
}
