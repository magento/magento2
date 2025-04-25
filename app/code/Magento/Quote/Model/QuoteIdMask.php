<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Quote\Model;

/**
 * QuoteIdMask model
 *
 * @method string getMaskedId()
 * @method QuoteIdMask setMaskedId(string $id)
 */
class QuoteIdMask extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $randomDataGenerator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Math\Random $randomDataGenerator
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Math\Random $randomDataGenerator,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
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
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\QuoteIdMask::class);
    }

    /**
     * Initialize quote identifier before save
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();
        if (empty($this->getMaskedId())) {
            $this->setMaskedId($this->randomDataGenerator->getUniqueHash());
        }
        return $this;
    }
}
