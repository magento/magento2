<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * ListIdMask model
 *
 * @method string getMaskedId()
 * @method ListIdMask setMaskedId()
 */
class ListIdMask extends AbstractModel
{
    /**
     * @var Random
     */
    protected $randomDataGenerator;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Random $randomDataGenerator
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Random $randomDataGenerator,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->randomDataGenerator = $randomDataGenerator;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Product\Compare\ListIdMask::class);
    }
}
