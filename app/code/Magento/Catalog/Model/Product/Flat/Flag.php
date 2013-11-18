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
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Calatog Product Flat Flag Model
 *
 * @category   Magento
 * @package    Magento_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Model\Product\Flat;

class Flag extends \Magento\Core\Model\Flag
{
    /**
     * Flag code
     *
     * @var string
     */
    protected $_flagCode = 'catalog_product_flat';

    /**
     * Retrieve flag data array
     *
     * @return array
     */
    public function getFlagData()
    {
        $flagData = parent::getFlagData();
        if (!is_array($flagData)) {
            $flagData = array();
            $this->setFlagData($flagData);
        }
        return $flagData;
    }

    /**
     * Retrieve Catalog Product Flat Data is built flag
     *
     * @return bool
     */
    public function getIsBuilt()
    {
        $flagData = $this->getFlagData();
        if (!isset($flagData['is_built'])) {
            $flagData['is_built'] = false;
            $this->setFlagData($flagData);
        }
        return (bool)$flagData['is_built'];
    }

    /**
     * Set Catalog Product Flat Data is built flag
     *
     * @param bool $flag
     *
     * @return \Magento\Catalog\Model\Product\Flat\Flag
     */
    public function setIsBuilt($flag)
    {
        $flagData = $this->getFlagData();
        $flagData['is_built'] = (bool)$flag;
        $this->setFlagData($flagData);
        return $this;
    }

    /**
     * Set Catalog Product Flat Data is built flag
     *
     * @deprecated after 1.7.0.0 use \Magento\Catalog\Model\Product\Flat\Flag::setIsBuilt() instead
     *
     * @param bool $flag
     *
     * @return \Magento\Catalog\Model\Product\Flat\Flag
     */
    public function setIsBuild($flag)
    {
        $this->setIsBuilt($flag);
        return $this;
    }
}
