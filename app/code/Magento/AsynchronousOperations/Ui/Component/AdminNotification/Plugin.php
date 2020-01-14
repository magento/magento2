<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Ui\Component\AdminNotification;

/**
 * Class Plugin to eliminate Bulk related links in the notification area
 */
class Plugin
{
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    private $authorization;

    /**
     * @var bool
     */
    private $isAllowed;

    /**
     * Plugin constructor.
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * Prepares Meta
     *
     * @param \Magento\AdminNotification\Ui\Component\DataProvider\DataProvider $dataProvider
     * @param array $result
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMeta(
        \Magento\AdminNotification\Ui\Component\DataProvider\DataProvider $dataProvider,
        $result
    ) {
        if (!isset($this->isAllowed)) {
            $this->isAllowed = $this->authorization->isAllowed(
                'Magento_Logging::system_magento_logging_bulk_operations'
            );
        }
        $result['columns']['arguments']['data']['config']['isAllowed'] = $this->isAllowed;
        return $result;
    }
}
