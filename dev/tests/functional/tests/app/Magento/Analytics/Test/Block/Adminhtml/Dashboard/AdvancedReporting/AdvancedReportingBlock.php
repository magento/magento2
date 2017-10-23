<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Block\Adminhtml\Dashboard\AdvancedReporting;

use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Advanced Reporting block.
 */
class AdvancedReportingBlock extends Modal
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
