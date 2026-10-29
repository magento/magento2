<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page;

use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Get target Url from routePath and store code.
 */
class TargetUrlBuilder implements TargetUrlBuilderInterface
{
    /**
     * @var UrlInterface
     */
    private $frontendUrlBuilder;

    /**
     * Initialize constructor
     *
     * @param UrlInterface $frontendUrlBuilder
     */
    public function __construct(UrlInterface $frontendUrlBuilder)
    {
        $this->frontendUrlBuilder = $frontendUrlBuilder;
    }

    /**
     * Get target URL
     *
     * @param string $routePath
     * @param string $store
     * @return string
     */
    public function process(string $routePath, string $store): string
    {
        return $this->frontendUrlBuilder->getUrl(
            $routePath,
            [
                '_current' => false,
                '_nosid' => true,
                '_query' => [
                    StoreManagerInterface::PARAM_NAME => $store
                ]
            ]
        );
    }
}
