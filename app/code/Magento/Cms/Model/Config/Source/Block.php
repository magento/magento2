<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
namespace Magento\Cms\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
=======
declare(strict_types=1);

namespace Magento\Cms\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

/**
 * Class Block
 */
<<<<<<< HEAD
class Block implements \Magento\Framework\Option\ArrayInterface
=======
class Block implements OptionSourceInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
{
    /**
     * @var array
     */
    private $options;

    /**
<<<<<<< HEAD
     * @var CollectionFactory
=======
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $collectionFactory;

    /**
<<<<<<< HEAD
     * @param CollectionFactory $collectionFactory
=======
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
<<<<<<< HEAD
     * To option array
     *
     * @return array
=======
     * {@inheritdoc}
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->collectionFactory->create()->toOptionIdArray();
        }
<<<<<<< HEAD
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return $this->options;
    }
}
