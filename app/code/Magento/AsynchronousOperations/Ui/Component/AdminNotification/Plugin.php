<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Ui\Component\AdminNotification;

use Magento\AdminNotification\Ui\Component\DataProvider\DataProvider;
use Magento\Framework\AuthorizationInterface;

/**
 * Class Plugin to eliminate Bulk related links in the notification area
 */
class Plugin
{
    private const BULK_LOGGING_ACL = "Magento_AsynchronousOperations::system_magento_logging_bulk_operations";

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var bool
     */
    private $isAllowed;

    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
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
            $this->isAllowed = $this->isAllowed();
        }
        $result['columns']['arguments']['data']['config']['isAllowed'] = $this->isAllowed;

        return $result;
    }

    /**
     * Check if it allowed to see bulk operations.
     *
     * @return bool
     */
    private function isAllowed(): bool
    {
        return $this->authorization->isAllowed(self::BULK_LOGGING_ACL);
    }
}
