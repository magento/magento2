<?php
/**
 * Bundle Option Type Source Model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Model\Source\Option;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\MetadataServiceInterface;

class Type extends \Magento\Framework\Model\AbstractExtensibleModel implements
    \Magento\Framework\Option\ArrayInterface,
    \Magento\Bundle\Api\Data\OptionTypeInterface
{
    /**#@+
     * Constants
     */
    const KEY_LABEL = 'label';
    const KEY_CODE = 'code';
    /**#@-*/

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MetadataServiceInterface $metadataService
     * @param AttributeValueFactory $customAttributeFactory
     * @param array $options
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MetadataServiceInterface $metadataService,
        AttributeValueFactory $customAttributeFactory,
        array $options,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        $this->options = $options;
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Get Bundle Option Type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = [];
        foreach ($this->options as $value => $label) {
            $types[] = ['label' => $label, 'value' => $value];
        }
        return $types;
    }

    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return $this->getData(self::KEY_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        return $this->getData(self::KEY_CODE);
    }

    /**
     * Set type label
     *
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        return $this->setData(self::KEY_LABEL, $label);
    }

    /**
     * Set type code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->setData(self::KEY_CODE, $code);
    }
    //@codeCoverageIgnoreEnd
}
