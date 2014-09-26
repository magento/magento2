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

class Save extends \Magento\Catalog\Controller\Adminhtml\Category
{
    /**
     * Filter category data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterCategoryPostData(array $rawData)
    {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            $data['image_additional_data'] = $data['image'];
            unset($data['image']);
        }
        return $data;
    }

    /**
     * Category save
     *
     * @return void
     */
    public function execute()
    {
        if (!($category = $this->_initCategory())) {
            return;
        }

        $storeId = $this->getRequest()->getParam('store');
        $refreshTree = 'false';
        $data = $this->getRequest()->getPost();
        if ($data) {
            $category->addData($this->_filterCategoryPostData($data['general']));
            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    if ($storeId) {
                        $parentId = $this->_objectManager->get(
                            'Magento\Framework\StoreManagerInterface'
                        )->getStore(
                            $storeId
                        )->getRootCategoryId();
                    } else {
                        $parentId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
                    }
                }
                $parentCategory = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentId);
            }

            /**
             * Process "Use Config Settings" checkboxes
             */
            $useConfig = $this->getRequest()->getPost('use_config');
            if ($useConfig) {
                foreach ($useConfig as $attributeCode) {
                    $category->setData($attributeCode, null);
                }
            }

            $category->setAttributeSetId($category->getDefaultAttributeSetId());

            if (isset($data['category_products']) && !$category->getProductsReadonly()) {
                $products = json_decode($data['category_products'], true);
                $category->setPostedProducts($products);
            }
            $this->_eventManager->dispatch(
                'catalog_category_prepare_save',
                array('category' => $category, 'request' => $this->getRequest())
            );

            /**
             * Check "Use Default Value" checkboxes values
             */
            $useDefaults = $this->getRequest()->getPost('use_default');
            if ($useDefaults) {
                foreach ($useDefaults as $attributeCode) {
                    $category->setData($attributeCode, false);
                }
            }

            /**
             * Proceed with $_POST['use_config']
             * set into category model for processing through validation
             */
            $category->setData('use_post_data_config', $this->getRequest()->getPost('use_config'));

            try {
                $validate = $category->validate();
                if ($validate !== true) {
                    foreach ($validate as $code => $error) {
                        if ($error === true) {
                            $attribute = $category->getResource()->getAttribute($code)->getFrontend()->getLabel();
                            throw new \Magento\Framework\Model\Exception(__('Attribute "%1" is required.', $attribute));
                        } else {
                            throw new \Magento\Framework\Model\Exception($error);
                        }
                    }
                }

                $category->unsetData('use_post_data_config');
                if (isset($data['general']['entity_id'])) {
                    throw new \Magento\Framework\Model\Exception(__('Unable to save the category'));
                }

                $category->save();
                $this->messageManager->addSuccess(__('You saved the category.'));
                $refreshTree = 'true';
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setCategoryData($data);
                $refreshTree = 'false';
            }
        }

        if ($this->getRequest()->getPost('return_session_messages_only')) {
            $category->load($category->getId());
            // to obtain truncated category name

            /** @var $block \Magento\Framework\View\Element\Messages */
            $block = $this->_objectManager->get('Magento\Framework\View\Element\Messages');
            $block->setMessages($this->messageManager->getMessages(true));
            $body = $this->_objectManager->get(
                'Magento\Core\Helper\Data'
            )->jsonEncode(
                array(
                    'messages' => $block->getGroupedHtml(),
                    'error' => $refreshTree !== 'true',
                    'category' => $category->toArray()
                )
            );
        } else {
            $url = $this->getUrl('catalog/*/edit', array('_current' => true, 'id' => $category->getId()));
            $body = '<script type="text/javascript">parent.updateContent("' .
                $url .
                '", {}, ' .
                $refreshTree .
                ');</script>';
        }

        $this->getResponse()->setBody($body);
    }
}
