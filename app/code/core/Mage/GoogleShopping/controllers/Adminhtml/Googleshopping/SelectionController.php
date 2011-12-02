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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * GoogleShopping Products selection grid controller
 *
 * @category    Mage
 * @package     Mage_GoogleShopping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Adminhtml_Googleshopping_SelectionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Search result grid with available products for Google Content
     */
    public function searchAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items_Product')
                ->setIndex($this->getRequest()->getParam('index'))
                ->setFirstShow(true)
                ->toHtml()
           );
    }

    /**
     * Grid with available products for Google Content
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock('Mage_GoogleShopping_Block_Adminhtml_Items_Product')
                ->setIndex($this->getRequest()->getParam('index'))
                ->toHtml()
           );
    }
}
