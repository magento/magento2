<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Setup\Patch\Data;

use Magento\Framework\Notification\NotifierInterface;

/**
 * Report Disable Notification
 */
class ReportDisableNotification implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**
     * @var NotifierInterface
     */
    private $notifier;

    /**
     * @param NotifierInterface $notifier
     */
    public function __construct(
        NotifierInterface $notifier
    ) {
        $this->notifier = $notifier;
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $message = <<<"MESSAGE"
To improve performance, collecting statistics for the Magento Report module is disabled by default. 
You can enable it in System Config.
MESSAGE;
        $this->notifier->addNotice(__('Disable Notice'), __($message));
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
