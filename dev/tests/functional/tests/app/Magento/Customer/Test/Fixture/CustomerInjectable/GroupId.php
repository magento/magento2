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

namespace Magento\Customer\Test\Fixture\CustomerInjectable;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Magento\Customer\Test\Fixture\CustomerGroupInjectable;

/**
 * Class GroupId
 * Addresses source for customer fixture
 */
class GroupId implements FixtureInterface
{
    /**
     * Source data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Source parameters
     *
     * @var array
     */
    protected $params;

    /**
     * Customer Group fixture
     *
     * @var array
     */
    protected $customerGroupFixture;

    /**
     * Source constructor
     *
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet'])) {
            /** @var CustomerGroupInjectable $customerGroup */
            $customerGroup = $fixtureFactory->createByCode('customerGroupInjectable', ['dataSet' => $data['dataSet']]);
            if (!$customerGroup->hasData('customer_group_id')) {
                $customerGroup->persist();
            }
            $this->data = $customerGroup->getCustomerGroupCode();
            $this->customerGroupFixture = $customerGroup;
        }
        if (isset($data['customerGroup']) && $data['customerGroup'] instanceof CustomerGroupInjectable) {
            $this->data = $data['customerGroup']->getCustomerGroupCode();
            $this->customerGroupFixture = $data['customerGroup'];
        }
    }

    /**
     * Persists prepared data into application
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param int|null $key [optional]
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }

    /**
     * Getting customerGroup fixture
     *
     * @return array
     */
    public function getCustomerGroup()
    {
        return $this->customerGroupFixture;
    }
}
