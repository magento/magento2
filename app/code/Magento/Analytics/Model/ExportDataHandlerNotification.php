<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Class which add notification behaviour to classes that handling of a new data collection for MBI.
 */
class ExportDataHandlerNotification implements ExportDataHandlerInterface
{
    /**
     * @var ExportDataHandlerInterface
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
    public function __construct(ExportDataHandlerInterface $exportDataHandler, Connector $connector)
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