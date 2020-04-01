<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Observer\MediaContent;

use Magento\Cms\Model\Page as CmsPage;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\MediaContentApi\Api\ModelProcessorInterface;

/**
 * Observe cms_page_save_after event and run processing relation between cms page content and media asset.
 */
class Page implements ObserverInterface
{
    private const CONTENT_TYPE = 'cms_page';

    /**
     * @var ModelProcessorInterface
     */
    private $processor;

    /**
     * @var array
     */
    private $fields;

    /**
     * @param ModelProcessorInterface $processor
     * @param array $fields
     */
    public function __construct(ModelProcessorInterface $processor, array $fields)
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
            $this->processor->execute(self::CONTENT_TYPE, $model, $this->fields);
        }
    }
}
