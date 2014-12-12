<?php
/**
 * Action to process Ogone offline data
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Ogone\Controller\Api;

class OfflineProcess extends \Magento\Ogone\Controller\Api
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->_validateOgoneData()) {
            $this->getResponse()->setHeader("Status", "404 Not Found");
            return false;
        }
        $this->_ogoneProcess();
    }
}
