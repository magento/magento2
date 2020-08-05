<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Ui\Component\Listing\Columns;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Helper\Wysiwyg\Images;
use Magento\Cms\Model\Wysiwyg\Images\Storage;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Overlay column
 */
class Url extends Column
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UrlInterface $urlInterface
     */
    private $urlInterface;

    /**
     * @var Images
     */
    private $images;

    /**
     * @var Storage
     */
    private $storage;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlInterface
     * @param Images $images
     * @param Storage $storage
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlInterface,
        Images $images,
        Storage $storage,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->images = $images;
        $this->storage = $storage;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['encoded_id'] = $this->images->idEncode($item['path']);
                $item[$this->getData('name')] = $this->getUrl($item[$this->getData('name')]);
            }
        }

        return $dataSource;
    }

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        parent::prepare();
        $this->setData(
            'config',
            array_replace_recursive(
                (array)$this->getData('config'),
                [
                    'onInsertUrl' => $this->urlInterface->getUrl('cms/wysiwyg_images/oninsert'),
                    'storeId' => $this->storeManager->getStore()->getId()
                ]
            )
        );
    }

    /**
     * Get URL for the provided media asset path
     *
     * @param string $path
     * @return string
     * @throws NoSuchEntityException
     */
    private function getUrl(string $path): string
    {
        return $this->storage->getThumbnailUrl($this->images->getStorageRoot() . $path);
    }
}
