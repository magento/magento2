<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

use Magento\Framework\EntityManager\HydratorInterface;
use Magento\ImportExport\Api\Data\ExportInfoInterface;
use Magento\ImportExport\Api\ExportManagementInterface;
use Magento\ImportExport\Model\ExportFactory;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * ExportManagementInterface implementation.
 */
class ExportManagement implements ExportManagementInterface
{
    /**
     * @var ExportFactory
     */
    private $exportModelFactory;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * ExportManagement constructor.
     * @param ExportFactory $exportModelFactory
     * @param HydratorInterface $hydrator
     * @param SerializerInterface $serializer
     */
    public function __construct(
        ExportFactory $exportModelFactory,
        HydratorInterface $hydrator,
        SerializerInterface $serializer
    ) {
        $this->exportModelFactory = $exportModelFactory;
        $this->hydrator = $hydrator;
        $this->serializer = $serializer;
    }

    /**
     * Export logic implementation.
     *
     * @param ExportInfoInterface $exportInfo
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export(ExportInfoInterface $exportInfo)
    {
        $arrData = $this->hydrator->extract($exportInfo);
        $arrData['export_filter'] = $this->serializer->unserialize($arrData['export_filter']);
        /** @var \Magento\ImportExport\Model\Export $exportModel */
        $exportModel = $this->exportModelFactory->create();
        $exportModel->setData($arrData);
        return $exportModel->export();
    }
}
