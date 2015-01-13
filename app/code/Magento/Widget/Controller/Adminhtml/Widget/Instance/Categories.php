<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

class Categories extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * Categories chooser Action (Ajax request)
     *
     * @return void
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $isAnchorOnly = $this->getRequest()->getParam('is_anchor_only', 0);
        $chooser = $this->_view->getLayout()->createBlock(
            'Magento\Catalog\Block\Adminhtml\Category\Widget\Chooser'
        )->setUseMassaction(
            true
        )->setId(
            $this->mathRandom->getUniqueHash('categories')
        )->setIsAnchorOnly(
            $isAnchorOnly
        )->setSelectedCategories(
            explode(',', $selected)
        );
        $this->setBody($chooser->toHtml());
    }
}
