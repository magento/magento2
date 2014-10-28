<?php
/**
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
namespace Magento\Catalog\Controller\Adminhtml\Category;

class Move extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * Move category action
     *
     * @return void
     */
    public function execute()
    {
        $category = $this->_initCategory();
        if (!$category) {
            $this->getResponse()->setBody(__('There was a category move error.'));
            return;
        }
        /**
         * New parent category identifier
         */
        $parentNodeId = $this->getRequest()->getPost('pid', false);
        /**
         * Category id after which we have put our category
         */
        $prevNodeId = $this->getRequest()->getPost('aid', false);

        try {
            $category->move($parentNodeId, $prevNodeId);
            $this->getResponse()->setBody('SUCCESS');
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->getResponse()->setBody($e->getMessage());
        } catch (\Exception $e) {
            $this->getResponse()->setBody(__('There was a category move error %1', $e));
            $this->_objectManager->get('Magento\Framework\Logger')->logException($e);
        }
    }
}
