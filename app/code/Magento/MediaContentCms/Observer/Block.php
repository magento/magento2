<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCms\Observer;

use Magento\Cms\Block\Block as CmsBlock;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\MediaContentApi\Api\UpdateRelationsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;

/**
 * Observe cms_block_save_after event and run processing relation between cms block content and media asset
 */
class Block implements ObserverInterface
{
    private const CONTENT_TYPE = 'cms_block';
    private const TYPE = 'entity_type';
    private const ENTITY_ID = 'entity_id';
    private const FIELD = 'field';

    /**
     * @var UpdateRelationsInterface
     */
    private $processor;

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
     * @param UpdateRelationsInterface $processor
     * @param array $fields
     */
    public function __construct(
        ContentIdentityInterfaceFactory $contentIdentityFactory,
        UpdateRelationsInterface $processor,
        array $fields
    ) {
        $this->contentIdentityFactory = $contentIdentityFactory;
        $this->processor = $processor;
        $this->fields = $fields;
    }

    /**
     * Retrieve the saved block and pass it to the model processor to save content - asset relations
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        $model = $observer->getEvent()->getData('object');

        if ($model instanceof CmsBlock) {
            foreach ($this->fields as $field) {
                if (!$model->dataHasChangedFor($field)) {
                    continue;
                }
                $this->processor->execute(
                    $this->contentIdentityFactory->create(
                        [
                            'data' => [
                                self::TYPE => self::CONTENT_TYPE,
                                self::FIELD => $field,
                                self::ENTITY_ID => (string) $model->getId(),
                            ]
                        ]
                    ),
                    (string) $model->getData($field)
                );
            }
        }
    }
}
