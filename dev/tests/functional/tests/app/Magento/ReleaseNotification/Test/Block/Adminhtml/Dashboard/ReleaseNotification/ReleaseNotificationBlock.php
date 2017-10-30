<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ReleaseNotification\Test\Block\Adminhtml\Dashboard\ReleaseNotification;

use Magento\Ui\Test\Block\Adminhtml\Modal;

/**
 * Release notification block.
 */
class ReleaseNotificationBlock extends Modal
{
    /**
     * @var string
     */
    private $releaseNotificationText = '[data-index="release_notification_text"]';

    /**
     * @inheritdoc
     */
    public function isVisible()
    {
        $this->waitModalAnimationFinished();
        return parent::isVisible() && $this->_rootElement->find($this->releaseNotificationText)->isVisible();
    }
}
