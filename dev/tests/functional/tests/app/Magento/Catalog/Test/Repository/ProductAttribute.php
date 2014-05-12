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

namespace Magento\Catalog\Test\Repository;

use Mtf\Repository\AbstractRepository;

/**
 * Class Product Attribute Repository
 *
 */
class ProductAttribute extends AbstractRepository
{
    /**
     * Construct
     *
     * @param array $defaultConfig
     * @param array $defaultData
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        $this->_data['default'] = array(
            'config' => $defaultConfig,
            'data' => $defaultData
        );

        $this->_data['configurable_attribute'] = $this->_data['default'];

        $this->_data['new_attribute'] = array(
            'config' => $defaultConfig,
            'data' => $this->buildNewAttributeData($defaultData),
        );
    }

    /**
     * Build new attribute data set
     *
     * @param array $defaultData
     * @return array
     */
    protected function buildNewAttributeData(array $defaultData)
    {
        unset($defaultData['fields']['is_configurable']);
        unset($defaultData['fields']['attribute_code']);
        return $defaultData;
    }
}
