<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Plugin\InventoryAdminUi\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryAdminUi\Ui\DataProvider\SourceDataProvider;

class PreventDisablingDefaultSourcePlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param RequestInterface $request
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        RequestInterface $request
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->request = $request;
    }

    /**
     * @param SourceDataProvider $subject
     * @param $meta
     * @return array
     */
    public function afterGetMeta(
        SourceDataProvider $subject,
        $meta
    ): array {
        $isFormComponent = SourceDataProvider::SOURCE_FORM_NAME === $subject->getName();
        if (!$isFormComponent || !$this->isDefaultSource()) {
            return $meta;
        }

        $meta['general'] = [
            'children' => [
                'enabled' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'disabled' => true,
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $meta;
    }

    /**
     * @return bool
     */
    private function isDefaultSource(): bool
    {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();
        $currentSourceCode = $this->request->getParam(SourceItemInterface::SOURCE_CODE);
        return $defaultSourceCode === $currentSourceCode;
    }
}
