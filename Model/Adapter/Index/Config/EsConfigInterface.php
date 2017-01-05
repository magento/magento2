<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

interface EsConfigInterface
{
    /**
     * @return array
     */
    public function getStemmerInfo();

    /**
     * @return array
     */
    public function getStopwordsInfo();
}
