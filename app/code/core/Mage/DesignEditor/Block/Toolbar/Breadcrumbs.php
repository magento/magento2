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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Breadcrumbs navigation for the current page
 */
class Mage_DesignEditor_Block_Toolbar_Breadcrumbs extends Mage_Core_Block_Template
{
    /**
     * Retrieve breadcrumbs for the current page location
     *
     * Result format:
     * array(
     *     array(
     *         'label' => 'Some Page Handle',
     *         'url'   => http://localhost/index.php/design/editor/page/page_type/some_page_type/',
     *     ),
     *     // ...
     * )
     *
     * @return array
     */
    public function getBreadcrumbs()
    {
        $layoutUpdate = $this->getLayout()->getUpdate();
        $result = array();
        $pageHandles = $this->_getPageHandlesPath();
        foreach ($pageHandles as $pageHandle) {
            $result[] = array(
                'label' => $this->escapeHtml($layoutUpdate->getPageHandleLabel($pageHandle)),
                'url'   => $this->getUrl('design/editor/page', array('handle' => $pageHandle))
            );
        }
        /** @var $blockHead Mage_Page_Block_Html_Head */
        $blockHead = $this->getLayout()->getBlock('head');
        if ($blockHead && !$this->getOmitCurrentPage()) {
            $result[] = array(
                'label' => $blockHead->getTitle(),
                'url'   => '',
            );
        } else if ($result) {
            $result[count($result) - 1]['url'] = '';
        }
        return $result;
    }

    /**
     * Return breadcrumbs path leading to the page handle selected
     *
     * @return array
     */
    protected function _getPageHandlesPath()
    {
        $layoutUpdate = $this->getLayout()->getUpdate();
        $pageHandles = $layoutUpdate->getPageHandles();
        if (!$pageHandles) {
            /** @var $controllerAction Mage_Core_Controller_Varien_Action */
            $controllerAction = Mage::app()->getFrontController()->getAction();
            if ($controllerAction) {
                $pageHandles = array($controllerAction->getDefaultLayoutHandle());
            }
        }
        if (count($pageHandles) == 1) {
            $pageHandle = reset($pageHandles);
            $pageHandles = $layoutUpdate->getPageHandleParents($pageHandle, false);
            $pageHandles[] = $pageHandle;
        }
        return $pageHandles;
    }
}
