<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Review\Test\Fixture;

use Mtf\Fixture\InjectableFixture;

/**
 * Class Rating
 */
class Rating extends InjectableFixture
{
    /**
     * @var string
     */
    protected $repositoryClass = 'Magento\Review\Test\Repository\Rating';

    /**
     * @var string
     */
    protected $handlerInterface = 'Magento\Review\Test\Handler\Rating\RatingInterface';

    protected $defaultDataSet = [
        'rating_code' => 'Rating %isolation%',
        'stores' => 'Main Website/Main Website Store/Default Store View',
        'is_active' => 'Yes',
    ];

    protected $rating_id = [
        'attribute_code' => 'rating_id',
        'backend_type' => 'smallint',
        'is_required' => '1',
        'default_value' => '',
        'input' => '',
    ];

    protected $entity_id = [
        'attribute_code' => 'entity_id',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
    ];

    protected $rating_code = [
        'attribute_code' => 'rating_code',
        'backend_type' => 'varchar',
        'is_required' => '',
        'default_value' => '',
        'input' => '',
        'group' => 'rating_information',
    ];

    protected $position = [
        'attribute_code' => 'position',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '0',
        'input' => '',
        'group' => 'rating_information',
    ];

    protected $is_active = [
        'attribute_code' => 'is_active',
        'backend_type' => 'smallint',
        'is_required' => '',
        'default_value' => '1',
        'input' => '',
        'group' => 'rating_information',
    ];

    protected $stores = [
        'attribute_code' => 'stores',
        'backend_type' => 'virtual',
        'group' => 'rating_information',
    ];

    protected $options = [
        'attribute_code' => 'options',
        'backend_type' => 'virtual',
        'group' => 'rating_information',
    ];

    public function getRatingId()
    {
        return $this->getData('rating_id');
    }

    public function getEntityId()
    {
        return $this->getData('entity_id');
    }

    public function getRatingCode()
    {
        return $this->getData('rating_code');
    }

    public function getPosition()
    {
        return $this->getData('position');
    }

    public function getIsActive()
    {
        return $this->getData('is_active');
    }

    public function getStores()
    {
        return $this->getData('stores');
    }

    public function getOptions()
    {
        return $this->getData('options');
    }
}
