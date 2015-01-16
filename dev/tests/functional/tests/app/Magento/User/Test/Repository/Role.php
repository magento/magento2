<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Test\Repository;

use Mtf\Factory\Factory;
use Mtf\Repository\AbstractRepository;

/**
 * Class Abstract Repository
 *
 */
class Role extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'config' => $defaultConfig,
            'data' => $defaultData,
        ];

        $this->initRoleTemplates();
        $this->initCustomRoles();
    }

    /**
     * Role templates with different scopes for custom filling with resources, sites or stores
     */
    protected function initRoleTemplates()
    {
        $dataTemplate = [
            'fields' => [
                'all' => [
                    'value' => 0,
                ],
                'gws_is_all' => [
                    'value' => 0,
                ],
                'rolename' => [
                    'value' => 'auto%isolation%',
                ],
            ],
        ];

        $this->_data['all_permissions_all_scopes']['data'] = $this->setPermissions(
            'all',
            $this->setScope('all', $dataTemplate)
        );

        $this->_data['all_permissions_website_scope']['data'] = $this->setPermissions(
            'all',
            $this->setScope('website', $dataTemplate)
        );

        $this->_data['all_permissions_store_scope']['data'] = $this->setPermissions(
            'all',
            $this->setScope('store', $dataTemplate)
        );

        $this->_data['custom_permissions_all_scopes']['data'] = $this->setPermissions(
            'custom',
            $this->setScope('all', $dataTemplate)
        );

        $this->_data['custom_permissions_website_scope']['data'] = $this->setPermissions(
            'custom',
            $this->setScope('website', $dataTemplate)
        );

        $this->_data['custom_permissions_store_scope']['data'] = $this->setPermissions(
            'custom',
            $this->setScope('store', $dataTemplate)
        );
    }

    /**
     * Init most popular custom roles
     */
    protected function initCustomRoles()
    {
        $resourceFixture = Factory::getFixtureFactory()->getMagentoUserResource();
        $salesAllScopes = $this->_data['custom_permissions_all_scopes']['data'];
        $salesAllScopes['fields']['resource']['value'] = $resourceFixture->get('Magento_Sales::sales');
        $this->_data['sales_all_scopes']['data'] = $salesAllScopes;
    }

    /**
     * Add role permission values to data array
     *
     * @param $permissions string. Possible values 'custom' or 'all'
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function setPermissions($permissions, $data = [])
    {
        if ('all' == $permissions) {
            $data['fields']['all']['value'] = 1;
        } elseif ('custom' == $permissions) {
            $data['fields']['all']['value'] = 0;
            $data['fields']['resource']['value'] = [];
        } else {
            throw new \InvalidArgumentException('Invalid permissions "' . $permissions . '"');
        }
        return $data;
    }

    /**
     * Set role scope: all, website or store
     *
     * @param string $scope possible values  'all', 'website', 'store'
     * @param array $data
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function setScope($scope, $data = [])
    {
        switch ($scope) {
            case 'all':
                $data['fields']['gws_is_all']['value'] = 1;
                break;
            case 'website':
                $data['fields']['gws_is_all']['value'] = 0;
                $data['fields']['gws_websites']['value'] = [];
                break;
            case 'store':
                $data['fields']['gws_is_all']['value'] = 0;
                $data['fields']['gws_store_groups']['value'] = [];
                break;
            default:
                throw new \InvalidArgumentException('Invalid role scope "' . $scope . '"');
        }
        return $data;
    }

    /**
     * Save custom data set to repository
     *
     * @param string $dataSetName
     * @param array $data
     */
    public function set($dataSetName, array $data)
    {
        $this->_data[$dataSetName]['data'] = $data;
    }
}
