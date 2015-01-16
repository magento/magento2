<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class CatalogSearchQuery
 */
class CatalogSearchQuery extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\CatalogSearch\Test\Repository\CatalogSearchQuery';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\CatalogSearch\Test\Handler\CatalogSearchQuery\CatalogSearchQueryInterface';

    protected $defaultDataSet = [
        'display_in_terms' => null,
        'is_active' => null,
        'updated_at' => null,
    ];

    protected $query_id = [
        'attribute_code' => 'query_id',
        'backend_type' => 'int',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $query_text = [
        'attribute_code' => 'query_text',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'source' => 'Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery\QueryText',
    ];

    protected $num_results = [
        'attribute_code' => 'num_results',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $popularity = [
        'attribute_code' => 'popularity',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $redirect = [
        'attribute_code' => 'redirect',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $synonym_for = [
        'attribute_code' => 'synonym_for',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $store_id = [
        'attribute_code' => 'store_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $display_in_terms = [
        'attribute_code' => 'display_in_terms',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
    ];

    protected $is_processed = [
        'attribute_code' => 'is_processed',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $updated_at = [
        'attribute_code' => 'updated_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => 'CURRENT_TIMESTAMP',
        'input' => '',
    ];

    public function getQueryId()
    {
        return $this->getData('query_id');
    }

    public function getQueryText()
    {
        return $this->getData('query_text');
    }

    public function getNumResults()
    {
        return $this->getData('num_results');
    }

    public function getPopularity()
    {
        return $this->getData('popularity');
    }

    public function getRedirect()
    {
        return $this->getData('redirect');
    }

    public function getSynonymFor()
    {
        return $this->getData('synonym_for');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getDisplayInTerms()
    {
        return $this->getData('display_in_terms');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getIsProcessed()
    {
        return $this->getData('is_processed');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }
}
