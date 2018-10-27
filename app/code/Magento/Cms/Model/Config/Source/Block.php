<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);

namespace Magento\Cms\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
=======
namespace Magento\Cms\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
>>>>>>> upstream/2.2-develop

/**
 * Class Block
 */
<<<<<<< HEAD
class Block implements OptionSourceInterface
=======
class Block implements \Magento\Framework\Option\ArrayInterface
>>>>>>> upstream/2.2-develop
{
    /**
     * @var array
     */
    private $options;

    /**
<<<<<<< HEAD
     * @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory
=======
     * @var CollectionFactory
>>>>>>> upstream/2.2-develop
     */
    private $collectionFactory;

    /**
<<<<<<< HEAD
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
=======
     * @param CollectionFactory $collectionFactory
>>>>>>> upstream/2.2-develop
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
<<<<<<< HEAD
     * {@inheritdoc}
=======
     * To option array
     *
     * @return array
>>>>>>> upstream/2.2-develop
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->collectionFactory->create()->toOptionIdArray();
        }
<<<<<<< HEAD

=======
>>>>>>> upstream/2.2-develop
        return $this->options;
    }
}
