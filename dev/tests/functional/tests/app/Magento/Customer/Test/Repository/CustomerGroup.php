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

namespace Magento\Customer\Test\Repository;

use Mtf\Factory\Factory;
use Mtf\Repository\AbstractRepository;

/**
 * Class Customer Group Repository
 *
 */
class CustomerGroup extends AbstractRepository
{
    /**
     * {inheritdoc}
     */
    public function __construct(array $defaultConfig = array(), array $defaultData = array())
    {
        $this->_data['default'] = array(
            'config' => $defaultConfig,
            'data' => $defaultData
        );

        $this->_data['valid_vat_id_domestic'] = $this->getValidDomestic($this->_data['default']);
        $this->_data['valid_vat_id_union'] = $this->getValidUnion($this->_data['default']);
        $this->_data['invalid_vat_id'] = $this->getInvalid($this->_data['default']);
        $this->_data['validation_error'] = $this->getError($this->_data['default']);
    }

    /**
     * Get Valid Domestic VAT group data
     *
     * @param array $defaultData
     * @return array
     */
    protected function getValidDomestic(array $defaultData)
    {
        $defaultData['data']['fields']['code']['value'] = 'Valid VAT ID-Domestic%isolation%';
        return $defaultData;
    }

    /**
     * Get Valid Union VAT group data
     *
     * @param array $defaultData
     * @return array
     */
    protected function getValidUnion(array $defaultData)
    {
        $defaultData['data']['fields']['code']['value'] = 'ValidVATID-IntraUnion%isolation%';
        return $defaultData;
    }

    /**
     * Get Invalid VAT group data
     *
     * @param array $defaultData
     * @return array
     */
    protected function getInvalid(array $defaultData)
    {
        $defaultData['data']['fields']['code']['value'] = 'Invalid VAT ID%isolation%';
        return $defaultData;
    }

    /**
     * Get Error group data
     *
     * @param array $defaultData
     * @return array
     */
    protected function getError(array $defaultData)
    {
        $defaultData['data']['fields']['code']['value'] = 'Validation Error Group%isolation%';
        return $defaultData;
    }
}
