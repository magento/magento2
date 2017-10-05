<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Class which add notification behaviour to classes that handling of a new data collection for MBI.
 */
class ExportDataHandlerNotification implements ExportDataHandlerInterface
{
    /**
     * @var ExportDataHandler
     */
    private $exportDataHandler;

    /**
     * @var Connector
     */
    private $analyticsConnector;

    /**
     * ExportDataHandlerNotification constructor.
     *
     * @param ExportDataHandlerInterface $exportDataHandler
     * @param Connector $connector
     */
    public function __construct(ExportDataHandler $exportDataHandler, Connector $connector)
    {
        $this->exportDataHandler = $exportDataHandler;
        $this->analyticsConnector = $connector;
    }

    /**
     * {@inheritdoc}
     * Execute notification command.
     *
     * @return bool
     */
    public function prepareExportData()
    {
        $result = $this->exportDataHandler->prepareExportData();
        $this->analyticsConnector->execute('notifyDataChanged');
        return $result;
    }
}
