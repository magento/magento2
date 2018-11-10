<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\UrlRewriteGraphQl\Model\Resolver\UrlRewrite;

/**
 * Pool of custom URL locators.
 */
class CustomUrlLocator implements CustomUrlLocatorInterface
{
    /**
     * @var CustomUrlLocatorInterface[]
     */
    private $urlLocators;

    /**
     * @param CustomUrlLocatorInterface[] $urlLocators
     */
    public function __construct(array $urlLocators = [])
    {
        $this->urlLocators = $urlLocators;
    }

    /**
     * @inheritdoc
     */
    public function locateUrl($urlKey): ?string
    {
        foreach ($this->urlLocators as $urlLocator) {
            $url = $urlLocator->locateUrl($urlKey);
            if ($url !== null) {
                return $url;
            }
        }
        return null;
    }
}
