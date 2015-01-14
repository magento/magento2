<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Block;

class Render extends \Magento\PageCache\Controller\Block
{
    /**
     * Returns block content depends on ajax request
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->_forward('noroute');
            return;
        }

        $blocks = $this->_getBlocks();
        $data = [];
        foreach ($blocks as $blockName => $blockInstance) {
            $data[$blockName] = $blockInstance->toHtml();
        }

        $this->getResponse()->setPrivateHeaders(\Magento\PageCache\Helper\Data::PRIVATE_MAX_AGE_CACHE);
        $this->translateInline->processResponseBody($data);
        $this->getResponse()->appendBody(json_encode($data));
    }
}
