<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\DataProvider\Plugin;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Sanitizer;
use Magento\Ui\Api\BookmarkManagementInterface;

class AddBookmarkAvailabilityFlag
{
    /**
     * @var BookmarkManagementInterface
     */
    private $bookmarkManagement;

    /**
     * @var Sanitizer
     */
    private $sanitizer;

    /**
     * AddBookmarkAvailabilityFlag constructor
     *
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param Sanitizer $sanitizer
     */
    public function __construct(
        BookmarkManagementInterface $bookmarkManagement,
        Sanitizer $sanitizer
    ) {
        $this->bookmarkManagement = $bookmarkManagement;
        $this->sanitizer = $sanitizer;
    }

    /**
     * Modify provider configuration and return meta
     *
     * @param DataProviderInterface $subject
     * @param array $meta
     * @return mixed
     */
    public function afterGetMeta(DataProviderInterface $subject, array $meta)
    {
        $this->modifyProviderConfigData($subject);

        return $meta;
    }

    /**
     * Modify provider configuration
     *
     * @param DataProviderInterface $dataProvider
     */
    private function modifyProviderConfigData(DataProviderInterface $dataProvider)
    {
        $configData = $dataProvider->getConfigData();
        if (!isset($configData['component'])
            || $configData['component'] !== 'Magento_Ui/js/grid/provider'
            || !isset($configData['namespace'])
        ) {
            return;
        }

        $bookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            'current',
            $configData['namespace']
        );

        $dataProvider->setConfigData($this->sanitizer->sanitize(
            array_replace(
                $configData,
                [
                    'firstLoad' => $bookmark !== null ? false : true
                ]
            )
        ));
    }
}
