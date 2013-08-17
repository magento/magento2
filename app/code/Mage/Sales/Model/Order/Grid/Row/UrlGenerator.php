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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales orders grid row url generator
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Order_Grid_Row_UrlGenerator extends Mage_Backend_Model_Widget_Grid_Row_UrlGenerator
{
    /**
     * @var Magento_AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @param Magento_AuthorizationInterface $authorization
     * @param array $args
     */
    public function __construct(Magento_AuthorizationInterface $authorization, array $args = array())
    {
        $this->_authorization = $authorization;
        parent::__construct($args);

    }

    /**
     * Generate row url
     * @param Varien_Object $item
     * @return bool|string
     */
    public function getUrl($item)
    {
        if ($this->_authorization->isAllowed('Mage_Sales::actions_view')) {
            return parent::getUrl($item);
        }
        return false;
    }
}
