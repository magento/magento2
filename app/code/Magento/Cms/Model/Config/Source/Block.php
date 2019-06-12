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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

/**
 * Class Block
 */
<<<<<<< HEAD
class Block implements \Magento\Framework\Option\ArrayInterface
=======
class Block implements OptionSourceInterface
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $collectionFactory;

    /**
<<<<<<< HEAD
     * @param CollectionFactory $collectionFactory
=======
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $collectionFactory
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options = $this->collectionFactory->create()->toOptionIdArray();
        }
<<<<<<< HEAD
=======

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        return $this->options;
    }
}
