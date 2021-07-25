<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ThemeGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreConfigInterface;
use Magento\Store\Api\StoreConfigManagerInterface;
use Magento\Store\Model\ResourceModel\StoreWebsiteRelation;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Api\Data\StoreInterface;

class HeaderLogoSrcResolver implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var Logo
     */
    private $logoBlock;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ValueFactory $valueFactory
     */
    public function __construct(ValueFactory $valueFactory, ScopeConfigInterface $scopeConfig, Logo $logoBlock)
    {
        $this->valueFactory = $valueFactory;
        $this->scopeConfig = $scopeConfig;  
        $this->logoBlock = $logoBlock; 
    }

    /**
     * Fetch and format configurable variants.
     *
     * {@inheritdoc}
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $result = function () use ($context) {
            $storeId = $context->getExtensionAttributes()->getStore()->getId();
            
            $logoSrc = $this->scopeConfig->getValue(
                'design/header/logo_src',
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            // Get the full path
            if ($logoSrc !== null) {
                $logoSrc = $this->logoBlock->getLogoSrc();
            }
            return $logoSrc;
        };

        return $this->valueFactory->create($result);
    }
}