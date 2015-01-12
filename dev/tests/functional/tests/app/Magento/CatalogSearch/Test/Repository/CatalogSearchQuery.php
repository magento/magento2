<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class CatalogSearchQuery
 * Data for creation Search Term
 */
class CatalogSearchQuery extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig [optional]
     * @param array $defaultData [optional]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'query_text' => ['value' => 'Query text %isolation%'],
            'store_id' => 'Main Website/Main Website Store/Default Store View',
            'synonym_for' => 'Synonym word %isolation%',
            'redirect' => 'http://example.com/',
            'display_in_terms' => 'No',
        ];
    }
}
