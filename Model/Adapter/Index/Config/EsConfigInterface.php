<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

interface EsConfigInterface
{
    /**
     * @return array
     */
    public function getStemmerInfo();
}
