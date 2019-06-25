<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Ui\Component\Listing\Column\Status;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;

/**
 * Class to transform Status options into a form of value-label pairs.
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options into array.
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $options = $this->collectionFactory->create()->toOptionArray();

            array_walk($options, function (&$option) {
                $option['__disableTmpl'] = true;
            });

            $this->options = $options;
        }

        return $this->options;
    }
}
