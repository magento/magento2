<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model\Sequence;

use Magento\SalesSequence\Model\Resource\Sequence\Meta as ResourceMetadata;
use Magento\SalesSequence\Model\SequenceFactory;

/**
 * Class SequenceBuilder
 */
class SequenceBuilder
{
    /**
     * @var resourceMetadata
     */
    protected $resourceMetadata;

    /**
     * @var SequenceFactory
     */
    protected $sequenceFactory;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * List of required sequence attribute
     *
     * @var array
     */
    protected $required = [
        'entityType',
        'storeId'
    ];

    /**
     * Default pattern for sequence creation, full list of attributes that can be defined by customer
     *
     * @var array
     */
    protected $pattern = [
        'entity_type',
        'store_id',
        'prefix',
        'suffix',
        'start_value',
        'step',
        'max_value',
        'warning_value',
    ];

    /**
     * Concrete data of sequence
     *
     * @var array
     */
    protected $data = [];

    /**
     * @param ResourceMetadata $resourceMetadata
     * @param SequenceFactory $sequenceFactory
     * @param MetaFactory $metaFactory
     * @param ProfileFactory $profileFactory
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        SequenceFactory $sequenceFactory,
        MetaFactory $metaFactory,
        ProfileFactory $profileFactory
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->sequenceFactory = $sequenceFactory;
        $this->metaFactory = $metaFactory;
        $this->profileFactory = $profileFactory;
        $this->data = array_flip($this->pattern);
    }


    /**
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->data['entity_type'] = $entityType;
        return $this;
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->data['store_id'] = $storeId;
        return $this;
    }

    /**
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->data['prefix'] = $prefix;
        return $this;
    }

    /**
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->data['suffix'] = $suffix;
        return $this;
    }

    /**
     * @param int $startValue
     * @return $this
     */
    public function setStartValue($startValue)
    {
        $this->data['start_value'] = $startValue;
        return $this;
    }

    /**
     * @param int $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->data['step'] = $step;
        return $this;
    }

    /**
     * @param int $maxValue
     * @return $this
     */
    public function setMaxValue($maxValue)
    {
        $this->data['max_value'] = $maxValue;
        return $this;
    }

    /**
     * @param int $warningValue
     * @return $this
     */
    public function setWarningValue($warningValue)
    {
        $this->data['warning_value'] = $warningValue;
        return $this;
    }

    protected function validate()
    {
        if (1!==1) {
            throw new \Magento\Framework\Exception\InputException('');
        }
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function create()
    {
        $this->validate();
        $meta = $this->resourceMetadata->loadByEntityTypeAndStore(
            $this->data['entity_type'],
            $this->data['store_id']
        );
        if ($meta->getId()) {
            throw new \Magento\Framework\Exception\AlreadyExistsException(
                __('Sequence with this metadata already exists')
            );
        }
        $this->data['sequence_table'] = sprintf(
            'sequence_%s_%s',
            $this->data['entity_type'],
            $this->data['store_id']
        );
        $this->data['is_active'] = 1;
        $profile = $this->profileFactory->create(
            [
                'data' => array_intersect_key(
                    $this->data,
                    array_flip(
                        [
                            'prefix', 'suffix', 'start_value', 'step', 'max_value', 'warning_value',
                            'is_active', 'active_profile'
                        ]
                    )
                )
            ]
        );
        $this->data['active_profile'] = $profile;
        $metadata = $this->metaFactory->create(
            [
                'data' => array_intersect_key(
                    $this->data,
                    array_flip(['entity_type', 'store_id', 'sequence_table', 'active_profile'])
                )
            ]
        );
        $this->resourceMetadata->save($metadata);
        $this->resourceMetadata->createSequence(
            $this->data['sequence_table'],
            $this->data['start_value']
        );
        $this->data = array_flip($this->pattern);
    }
}