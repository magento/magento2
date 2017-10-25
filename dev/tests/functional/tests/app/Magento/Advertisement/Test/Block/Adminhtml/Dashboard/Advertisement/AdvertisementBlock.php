<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Advertisement\Test\Block\Adminhtml\Dashboard\Advertisement;

use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Advertisement block.
 */
class AdvertisementBlock extends Modal
{
    /**
     * @var string
     */
    private $advertisementText = '[data-index="advertisement_text"]';

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        $this->waitModalAnimationFinished();
        return parent::isVisible() && $this->_rootElement->find($this->advertisementText)->isVisible();
    }
}
