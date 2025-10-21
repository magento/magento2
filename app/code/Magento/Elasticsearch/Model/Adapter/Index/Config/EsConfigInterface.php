<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

/**
 * @api
 * @since 100.1.0
 */
interface EsConfigInterface
{
    /**
     * @return array
     * @since 100.1.0
     */
    public function getStemmerInfo();

    /**
     * @return array
     * @since 100.1.0
     */
    public function getStopwordsInfo();
}
