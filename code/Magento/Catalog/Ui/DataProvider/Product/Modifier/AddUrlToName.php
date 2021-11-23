<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Ui\DataProvider\Product\Modifier;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

/**
 * Modify product listing price attributes
 */
class AddUrlToName implements ModifierInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Http
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * AddUrlToName constructor.
     *
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param Http $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Escaper $escaper,
        Http $request,
        StoreManagerInterface $storeManager
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->request = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        if (empty($data)) {
            return $data;
        }

        $storeId = $this->getStore()->getId();

        if (!empty($data['items'])) {
            foreach ($data['items'] as &$item) {
                if (isset($item['name'])) {
                    $url = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $item['entity_id'], 'store' => $storeId]
                    );
                    $item['name'] = '<a href="javascript:;" onclick="window.open(\'' . $url . '\', \'_blank\');">'
                        . $this->escaper->escapeHtml(
                            $item['name']
                        ) . '</a>';
                }
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta): array
    {
        return $meta;
    }

    /**
     * Retrieve store
     *
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    private function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }
}
