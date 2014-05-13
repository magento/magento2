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

namespace Magento\User\Test\Repository;

use Mtf\Repository\AbstractRepository;
use Mtf\Factory\Factory;

/**
 * Class Abstract Repository
 *
 */
class Role extends AbstractRepository
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        $this->_data['default'] = array(
            'config' => $defaultConfig,
            'data' => $defaultData
        );

        $this->initRoleTemplates();
        $this->initCustomRoles();
    }

    /**
     * Role templates with different scopes for custom filling with resources, sites or stores
     */
    protected function initRoleTemplates()
    {
        $dataTemplate = array(
            'fields' => array(
                'all' => array(
                    'value' => 0,
                ),
                'gws_is_all' => array(
                    'value' => 0,
                ),
                'rolename' => array(
                    'value' => 'auto%isolation%',
                ),
            )
        );

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
    protected function setPermissions($permissions, $data = array())
    {
        if ('all' == $permissions) {
            $data['fields']['all']['value'] = 1;
        } elseif ('custom' == $permissions) {
            $data['fields']['all']['value'] = 0;
            $data['fields']['resource']['value'] = array();
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
    protected function setScope($scope, $data = array())
    {
        switch ($scope) {
            case 'all':
                $data['fields']['gws_is_all']['value'] = 1;
                break;
            case 'website':
                $data['fields']['gws_is_all']['value'] = 0;
                $data['fields']['gws_websites']['value'] = array();
                break;
            case 'store':
                $data['fields']['gws_is_all']['value'] = 0;
                $data['fields']['gws_store_groups']['value'] = array();
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
