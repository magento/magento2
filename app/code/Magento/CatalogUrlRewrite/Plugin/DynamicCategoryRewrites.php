<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\Storage\DynamicStorage;
use Magento\CatalogUrlRewrite\Model\Storage\DbStorage;

/**
 * Class DbStorage
 */
class DynamicCategoryRewrites
{
    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var DynamicStorage
     */
    private $dynamicStorage;

    /**
     * @param ScopeConfigInterface|null $config
     * @param DynamicStorage $dynamicStorage
     */
    public function __construct(
        ScopeConfigInterface $config,
        DynamicStorage $dynamicStorage
    ) {
        $this->config = $config;
        $this->dynamicStorage = $dynamicStorage;
    }

    /**
     * Check config value of generate_category_product_rewrites
     *
     * @return bool
     */
    private function isCategoryRewritesEnabled(): bool
    {
        return (bool)$this->config->getValue('catalog/seo/generate_category_product_rewrites');
    }

    /**
     * Execute proxy
     *
     * @param callable $proceed
     * @param array $data
     * @param string $functionName
     * @return mixed
     */
    private function proxy(callable $proceed, array $data, string $functionName)
    {
        if ($this->isCategoryRewritesEnabled()) {
            return $proceed($data);
        }

        return $this->dynamicStorage->$functionName($data);
    }

    /**
     * Find rewrite by specific data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param DbStorage $subject
     * @param callable $proceed
     * @param array $data
     * @return UrlRewrite|null
     */
    public function aroundFindOneByData(DbStorage $subject, callable $proceed, array $data)
    {
        return $this->proxy($proceed, $data, 'findOneByData');
    }

    /**
     * Find rewrites by specific data
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param DbStorage $subject
     * @param callable $proceed
     * @param array $data
     * @return UrlRewrite[]
     */
    public function aroundFindAllByData(DbStorage $subject, callable $proceed, array $data)
    {
        return $this->proxy($proceed, $data, 'findAllByData');
    }
}
