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

namespace Magento\Store\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Store
 * Data for creation Store
 */
class Store extends AbstractRepository
{
    /**
     * @constructor
     * @param array $defaultConfig
     * @param array $defaultData
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(array $defaultConfig = [], array $defaultData = [])
    {
        $this->_data['default'] = [
            'group_id' => ['dataSet' => 'default'],
            'name' => 'Custom_Store_%isolation%',
            'code' => 'code_%isolation%',
            'is_active' => 'Enabled',
        ];

        $this->_data['default_store_view'] = [
            'store_id' => 1,
            'name' => 'Default Store View',
        ];

        $this->_data['All Store Views'] = [
            'name' => 'All Store Views',
            'store_id' => 0,
        ];

        $this->_data['german'] = [
            'group_id' => ['dataSet' => 'default'],
            'name' => 'DE%isolation%',
            'code' => 'de%isolation%',
            'is_active' => 'Enabled',
        ];
    }
}
