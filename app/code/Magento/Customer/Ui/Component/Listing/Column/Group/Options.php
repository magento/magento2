<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\Listing\Column\Group;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Class Options
 * @since 2.0.0
 */
class Options implements OptionSourceInterface
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
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @since 2.0.0
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = $this->collectionFactory->create()->toOptionArray();
        }
        return $this->options;
    }
}
