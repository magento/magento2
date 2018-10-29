<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Plugin;

/**
 * Enable Product EAV indexer in configuration for MySQL search engine
 *
 * @deprecated
 * @see \Magento\ElasticSearch
 */
class EnableEavIndexer
{
    /**
     * Config search engine path
     */
    const SEARCH_ENGINE_VALUE_PATH = 'groups/search/fields/engine/value';

    /**
     * @param \Magento\Config\Model\Config $subject
     */
    public function beforeSave(\Magento\Config\Model\Config $subject)
    {
        $searchEngine = $subject->getData(self::SEARCH_ENGINE_VALUE_PATH);
        if ($searchEngine === 'mysql') {
            $data = $subject->getData();
            $data['groups']['search']['fields']['enable_eav_indexer']['value'] = 1;

            $subject->setData($data);
        }
    }
}
