<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Ajax;

class Suggest extends \Magento\Framework\App\Action\Action
{
    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            $this->getResponse()->setRedirect($this->_url->getBaseUrl());
        }

        $suggestData = $this->_objectManager->get('Magento\Search\Helper\Data')->getSuggestData();
        $this->getResponse()->representJson(json_encode($suggestData));
    }
}
