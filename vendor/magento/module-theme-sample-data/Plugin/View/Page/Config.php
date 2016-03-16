<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ThemeSampleData\Plugin\View\Page;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config as PageConfig;

class Config
{
    /**
     * Url configuration
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $baseUrl;

    /**
     * @param UrlInterface $baseUrl
     */
    public function __construct(
        \Magento\Framework\UrlInterface $baseUrl
    ) {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param PageConfig $subject
     * @param string $result
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetIncludes(PageConfig $subject, $result)
    {
        $pattern = '{{MEDIA_URL}}';
        if (strpos($result, $pattern) !== false) {
            $url = $this->baseUrl->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
            $result = str_replace($pattern, $url, $result);
        }
        return $result;
    }
}
