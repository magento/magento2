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

namespace Magento\User\Test\Fixture;

use Mtf\Factory\Factory;
use Mtf\Fixture\DataFixture;

/**
 * Class Role
 *
 */
class Role extends DataFixture
{
    /**
     * {@inheritdoc}
     */
    public function persist()
    {
        return Factory::getApp()->magentoUserCreateRole($this);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initData()
    {
        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoUserRole($this->_dataConfig, $this->_data);
    }

    /**
     * Set custom ACL resource array to role
     *
     * @param array $resource
     */
    public function setResource(array $resource)
    {
        $this->_data['fields']['resource']['value'] = $resource;
    }

    /**
     * Merge resource array with existing values
     *
     * @param array $resource
     */
    public function addResource(array $resource)
    {
        $this->_data['fields']['resource']['value'] = array_merge_recursive(
            $this->_data['fields']['resource']['value'],
            $resource
        );
    }

    /**
     * Set websites of stores if current data set works with them
     *
     * @param array $items
     * @throws \InvalidArgumentException
     */
    public function setScopeItems(array $items)
    {
        if (array_key_exists('gws_store_groups', $this->_data['fields'])) {
            $scope = 'gws_store_groups';
        } elseif (array_key_exists('gws_websites', $this->_data['fields'])) {
            $scope = 'gws_websites';
        } else {
            throw new \InvalidArgumentException('Current data set doesn\'t work with stores and websites');
        }
        $this->_data['fields'][$scope]['value'] = $items;
    }

    /**
     * Convert data from canonical array to repository native format
     *
     * @param array $data
     * @return array
     */
    protected function convertData(array $data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            $result['fields'][$key]['value'] = $value;
        }
        return $result;
    }

    /**
     * Save custom data set to repository
     *
     * @param string $name
     * @param array $data
     * @param bool $convert convert data from canonical array to repository native format
     */
    public function save($name, array $data, $convert = true)
    {
        if ($convert) {
            $data = $this->convertData($data);
        }
        $this->_repository->set($name, $data);
    }
}
