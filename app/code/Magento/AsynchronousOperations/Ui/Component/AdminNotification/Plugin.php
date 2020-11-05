<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Ui\Component\AdminNotification;

use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\AsynchronousOperations\Model\AccessManager;

/**
 * Class Plugin to eliminate Bulk related links in the notification area
 */
class Plugin
{
    /**
     * @var AccessManager
     */
    private $accessManager;

    /**
     * @var bool
     */
    private $isAllowed;

    /**
     * @param AccessManager $accessManager
     */
    public function __construct(
        AccessManager $accessManager
    ) {
        $this->accessManager = $accessManager;
    }

    /**
     * Prepares Meta
     *
     * @param DataProvider $dataProvider
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(DataProvider $dataProvider, $result)
    {
        if (!isset($this->isAllowed)) {
            $this->isAllowed = $this->accessManager->isOwnActionsAllowed();
        }
        $result['columns']['arguments']['data']['config']['isAllowed'] = $this->isAllowed;

        return $result;
    }
}
