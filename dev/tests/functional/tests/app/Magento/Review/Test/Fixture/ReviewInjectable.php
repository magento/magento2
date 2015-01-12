<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class ReviewInjectable
 * Product review fixture
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ReviewInjectable extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Review\Test\Repository\ReviewInjectable';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Review\Test\Handler\ReviewInjectable\ReviewInjectableInterface';

    /**
     * Default data
     *
     * @var array
     */
    protected $defaultDataSet = [
        'status_id' => 'Approved',
        'select_stores' => ['Main Website/Main Website Store/Default Store View'],
        'nickname' => 'Guest customer %isolation%',
        'title' => 'Summary review %isolation%',
        'detail' => 'Text review %isolation%',
        'ratings' => [
            [
                'dataSet' => 'visibleOnDefaultWebsite',
                'rating' => 4,
            ],
        ],
        'entity_id' => ['dataSet' => 'catalogProductSimple::default'],
        'type' => 'Administrator',
    ];

    protected $review_id = [
        'attribute_code' => 'review_id',
        'backend_type' => 'bigint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $created_at = [
        'attribute_code' => 'created_at',
        'backend_type' => 'timestamp',
        'is_required' => '',
        'default_value' => 'CURRENT_TIMESTAMP',
        'input' => '',
    ];

    protected $entity_id = [
        'attribute_code' => 'entity_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'source' => 'Magento\Review\Test\Fixture\ReviewInjectable\EntityId',
    ];

    protected $entity_pk_value = [
        'attribute_code' => 'entity_pk_value',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $status_id = [
        'attribute_code' => 'status_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $detail_id = [
        'attribute_code' => 'detail_id',
        'backend_type' => 'bigint',
        'is_required' => '1',
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

    protected $title = [
        'attribute_code' => 'title',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $detail = [
        'attribute_code' => 'detail',
        'backend_type' => 'text',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $nickname = [
        'attribute_code' => 'nickname',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $customer_id = [
        'attribute_code' => 'customer_id',
        'backend_type' => 'int',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    protected $select_stores = [
        'attribute_code' => 'select_stores',
        'backend_type' => 'virtual',
        'is_required' => '1',
        'default_value' => '0',
        'input' => 'multiselectgrouplist',
    ];

    protected $ratings = [
        'attribute_code' => 'ratings',
        'backend_type' => 'virtual',
        'source' => 'Magento\Review\Test\Fixture\ReviewInjectable\Ratings',
    ];

    protected $type = [
        'attribute_code' => 'type',
        'backend_type' => 'string',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
    ];

    public function getType()
    {
        return $this->getData('type');
    }

    public function getSelectStores()
    {
        return $this->getData('select_stores');
    }

    public function getReviewId()
    {
        return $this->getData('review_id');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function getEntityPkValue()
    {
        return $this->getData('entity_pk_value');
    }

    public function getStatusId()
    {
        return $this->getData('status_id');
    }

    public function getDetailId()
    {
        return $this->getData('detail_id');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    public function getTitle()
    {
        return $this->getData('title');
    }

    public function getDetail()
    {
        return $this->getData('detail');
    }

    public function getNickname()
    {
        return $this->getData('nickname');
    }

    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    public function getRatings()
    {
        return $this->getData('ratings');
    }
}
