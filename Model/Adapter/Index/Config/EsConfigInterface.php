<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

/**
 * @api
 * @since 2.1.0
 */
interface EsConfigInterface
{
    /**
     * @return array
     * @since 2.1.0
     */
    public function getStemmerInfo();

    /**
     * @return array
     * @since 2.1.0
     */
    public function getStopwordsInfo();
}
