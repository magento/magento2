<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
     * @param ExportDataHandlerInterface $exportDataHandler
     * @param Connector $connector
     */
    public function __construct(ExportDataHandler $exportDataHandler, Connector $connector)
    {
        $this->exportDataHandler = $exportDataHandler;
        $this->analyticsConnector = $connector;
    }

    /**
     * @inheritdoc
     */
    public function prepareExportData()
    {
        $result = $this->exportDataHandler->prepareExportData();
        $this->analyticsConnector->execute('notifyDataChanged');
        return $result;
    }
}
