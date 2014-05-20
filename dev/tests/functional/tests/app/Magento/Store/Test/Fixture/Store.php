<?php
/**
 * Store fixture
 *
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

namespace Magento\Store\Test\Fixture;

use Mtf\Fixture\DataFixture;
use Mtf\Factory\Factory;

class Store extends DataFixture
{
    /**
     * @param \Mtf\System\Config $configuration
     * @param array $placeholders
     */
    public function __construct(\Mtf\System\Config $configuration, array $placeholders = array())
    {
        parent::__construct($configuration, $placeholders);
        $this->_placeholders = $placeholders;
    }

    /**
     * Initialize fixture data
     */
    protected function _initData()
    {
        $this->_data = array(
            'fields' => array(
                'group' => array(
                    'value' => 'Main Website Store',
                    'input' => 'select'
                ),
                'name' => array(
                    'value' => 'DE%isolation%'
                ),
                'code' => array(
                    'value' => 'de%isolation%'
                ),
                'is_active' => array(
                    'value' => 'Enabled',
                    'input' => 'select',
                ),
            )
        );

        $this->_repository = Factory::getRepositoryFactory()
            ->getMagentoStoreCustomStore($this->_dataConfig, $this->_data);
    }


    /**
     * Create Store
     *
     * @return Store
     */
    public function persist()
    {
        return Factory::getApp()->magentoStoreCreateStore($this);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('fields/name/value');
    }
}
