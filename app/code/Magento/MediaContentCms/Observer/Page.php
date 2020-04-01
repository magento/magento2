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
use Magento\Framework\Model\AbstractModel;
use Magento\MediaContentApi\Api\UpdateRelationsInterface;

/**
 * Observe cms_page_save_after event and run processing relation between cms page content and media asset.
 */
class Page implements ObserverInterface
{
    private const CONTENT_TYPE = 'cms_page';

    /**
     * @var UpdateRelationsInterface
     */
    private $processor;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param UpdateRelationsInterface $processor
     * @param array $fields
     */
    public function __construct(UpdateRelationsInterface $processor, array $fields)
    {
        $this->processor = $processor;
        $this->fields = $fields;
    }

    /**
     * Retrieve the saved page and pass it to the model processor to save content - asset relations
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var CmsPage $model */
        $model = $observer->getEvent()->getData('object');
        if ($model instanceof AbstractModel) {
            $this->updateRelations($model);
        }
    }

    /**
     * Update relations for the model
     *
     * @param AbstractModel $model
     */
    private function updateRelations(AbstractModel $model): void
    {
        foreach ($this->fields as $field) {
            if (!$model->dataHasChangedFor($field)) {
                continue;
            }
            $this->processor->execute(
                self::CONTENT_TYPE,
                $field,
                (string) $model->getId(),
                (string) $model->getData($field)
            );
        }
    }
}
