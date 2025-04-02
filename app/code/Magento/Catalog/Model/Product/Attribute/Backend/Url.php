<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Url extends AbstractBackend
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @param ScopeConfigInterface $config
     */
    public function __construct(ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * Set Attribute instance, Rewrite for redefine attribute scope
     *
     * @param Attribute $attribute
     * @return $this
     */
    public function setAttribute($attribute)
    {
        parent::setAttribute($attribute);
        $this->setScope($attribute);
        return $this;
    }

    /**
     * Redefine Attribute scope
     *
     * @param Attribute $attribute
     * @return void
     */
    private function setScope(Attribute $attribute): void
    {
        if ($this->config->getValue(
            ProductScopeRewriteGenerator::URL_REWRITE_SCOPE_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        ) == ProductScopeRewriteGenerator::WEBSITE_URL_REWRITE_SCOPE) {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_WEBSITE);
        } else {
            $attribute->setIsGlobal(ScopedAttributeInterface::SCOPE_STORE);
        }
    }
}
