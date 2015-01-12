<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
        $result = [];
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
