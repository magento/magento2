<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Model;

use Magento\Framework\App\ResourceConnection as AppResource;
use Magento\Framework\DB\Ddl\Sequence as DdlSequence;
use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Webapi\Exception;
use Magento\SalesSequence\Model\ResourceModel\Meta as ResourceMetadata;
use Psr\Log\LoggerInterface as Logger;

/**
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Builder implements ResetAfterRequestInterface
{
    /**
     * @var resourceMetadata
     */
    protected $resourceMetadata;

    /**
     * @var ProfileFactory
     */
    protected $profileFactory;

    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @var AppResource
     */
    protected $appResource;

    /**
     * @var DdlSequence
     */
    protected $ddlSequence;

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
     * @var Logger
     */
    protected $logger;

    /**
     * @param ResourceMetadata $resourceMetadata
     * @param MetaFactory $metaFactory
     * @param ProfileFactory $profileFactory
     * @param AppResource $appResource
     * @param DdlSequence $ddlSequence
     * @param Logger $logger
     */
    public function __construct(
        ResourceMetadata $resourceMetadata,
        MetaFactory $metaFactory,
        ProfileFactory $profileFactory,
        AppResource $appResource,
        DdlSequence $ddlSequence,
        Logger $logger
    ) {
        $this->resourceMetadata = $resourceMetadata;
        $this->metaFactory = $metaFactory;
        $this->profileFactory = $profileFactory;
        $this->appResource = $appResource;
        $this->ddlSequence = $ddlSequence;
        $this->logger = $logger;
        $this->data = array_flip($this->pattern);
    }

    /**
     * Set entity type data
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->data['entity_type'] = $entityType;
        return $this;
    }

    /**
     * Set store id data
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->data['store_id'] = $storeId;
        return $this;
    }

    /**
     * Set prefix data
     *
     * @param string $prefix
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->data['prefix'] = $prefix;
        return $this;
    }

    /**
     * Set suffix data
     *
     * @param string $suffix
     * @return $this
     */
    public function setSuffix($suffix)
    {
        $this->data['suffix'] = $suffix;
        return $this;
    }

    /**
     * Set start value data
     *
     * @param int $startValue
     * @return $this
     */
    public function setStartValue($startValue)
    {
        $this->data['start_value'] = $startValue;
        return $this;
    }

    /**
     * Set step data
     *
     * @param int $step
     * @return $this
     */
    public function setStep($step)
    {
        $this->data['step'] = $step;
        return $this;
    }

    /**
     * Set max value data
     *
     * @param int $maxValue
     * @return $this
     */
    public function setMaxValue($maxValue)
    {
        $this->data['max_value'] = $maxValue;
        return $this;
    }

    /**
     * Set warning value data
     *
     * @param int $warningValue
     * @return $this
     */
    public function setWarningValue($warningValue)
    {
        $this->data['warning_value'] = $warningValue;
        return $this;
    }

    /**
     * Returns sequence table name
     *
     * @return string
     */
    protected function getSequenceName()
    {
        return $this->appResource->getTableName(
            sprintf(
                'sequence_%s_%s',
                $this->data['entity_type'],
                $this->data['store_id']
            )
        );
    }

    /**
     * Create sequence with metadata and profile
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @return void
     */
    public function create()
    {
        $metadata = $this->resourceMetadata->loadByEntityTypeAndStore(
            $this->data['entity_type'],
            $this->data['store_id']
        );
        if ($metadata->getSequenceTable() == $this->getSequenceName()) {
            return;
        }
        $this->data['sequence_table'] = $this->getSequenceName();
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
        $profile->setHasDataChanges(true);
        $this->data['active_profile'] = $profile;
        $metadata = $this->metaFactory->create(
            [
                'data' => array_intersect_key(
                    $this->data,
                    array_flip(['entity_type', 'store_id', 'sequence_table', 'active_profile'])
                )
            ]
        );
        $metadata->setHasDataChanges(true);
        try {
            $this->resourceMetadata->save($metadata);
            $connection = $this->appResource->getConnection('sales');
            if (!$connection->isTableExists($this->data['sequence_table'])) {
                $connection->query(
                    $this->ddlSequence->getCreateSequenceDdl(
                        $this->data['sequence_table'],
                        $this->data['start_value']
                    )
                );
            }
        } catch (Exception $e) {
            $this->resourceMetadata->delete($metadata);
            $this->logger->critical($e);
            throw $e;
        }
        $this->data = array_flip($this->pattern);
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->data = [];
    }
}
