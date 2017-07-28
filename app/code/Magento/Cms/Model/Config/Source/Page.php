<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class Page
 * @since 2.0.0
 */
class Page implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * To option array
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->collectionFactory->create()->toOptionIdArray();
        }
        return $this->options;
    }
}
