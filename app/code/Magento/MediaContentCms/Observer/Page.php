<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Observer;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Model\Config;

/**
 * Observe cms_page_save_after event and run processing relation between cms page content and media asset.
 */
class Page implements ObserverInterface
{
    private const CONTENT_TYPE = 'cms_page';
    private const TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @param ContentIdentityInterfaceFactory $contentIdentityFactory
     * @param UpdateContentAssetLinksInterface $updateContentAssetLinks
     * @param Config $config
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        UpdateContentAssetLinksInterface $updateContentAssetLinks,
        Config $config,
        array $fields
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->updateContentAssetLinks = $updateContentAssetLinks;
        $this->config = $config;
        $this->fields = $fields;
    }

    /**
     * Retrieve the saved page and pass it to the model processor to save content - asset relations
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled()) {
            return;
        }

        $model = $observer->getEvent()->getData('object');

        if ($model instanceof CmsPage) {
            foreach ($this->fields as $field) {
                if (!$model->dataHasChangedFor($field)) {
                    continue;
                }
                $this->updateContentAssetLinks->execute(
                    $this->contentIdentityFactory->create(
                        [
                            self::TYPE => self::CONTENT_TYPE,
                            self::FIELD => $field,
                            self::ENTITY_ID => (string) $model->getId(),
                        ]
                    ),
                    (string) $model->getData($field)
                );
            }
        }
    }
}
