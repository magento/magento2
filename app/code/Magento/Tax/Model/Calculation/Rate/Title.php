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
 * @category    Magento
 * @package     Magento_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tax Rate Title Model
 *
 * @method \Magento\Tax\Model\Resource\Calculation\Rate\Title _getResource()
 * @method \Magento\Tax\Model\Resource\Calculation\Rate\Title getResource()
 * @method int getTaxCalculationRateId()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setTaxCalculationRateId(int $value)
 * @method int getStoreId()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setStoreId(int $value)
 * @method string getValue()
 * @method \Magento\Tax\Model\Calculation\Rate\Title setValue(string $value)
 *
 * @category    Magento
 * @package     Magento_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Tax\Model\Calculation\Rate;

class Title extends \Magento\Core\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magento\Tax\Model\Resource\Calculation\Rate\Title');
    }

    public function deleteByRateId($rateId)
    {
        $this->getResource()->deleteByRateId($rateId);
        return $this;
    }
}
