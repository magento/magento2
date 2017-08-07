<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogUrlRewrite\Model;

use Magento\UrlRewrite\Model\UrlPersistInterface;

/**
 * Class \Magento\CatalogUrlRewrite\Model\UrlRewriteBunchReplacer
 *
 * @since 2.2.0
 */
class UrlRewriteBunchReplacer
{
    /**
     * @var UrlPersistInterface
     * @since 2.2.0
     */
    private $urlPersist;

    /**
     * @param UrlPersistInterface $urlPersist
     * @since 2.2.0
     */
    public function __construct(UrlPersistInterface $urlPersist)
    {
        $this->urlPersist = $urlPersist;
    }

    /**
     * Do Bunch Replace, with default bunch value = 10000
     *
     * @param array $urls
     * @param int $bunchSize
     * @return void
     * @since 2.2.0
     */
    public function doBunchReplace(array $urls, $bunchSize = 10000)
    {
        foreach (array_chunk($urls, $bunchSize) as $urlsBunch) {
            $this->urlPersist->replace($urlsBunch);
        }
    }
}
