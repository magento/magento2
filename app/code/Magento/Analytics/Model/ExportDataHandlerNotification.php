<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

/**
 * Class which add notification behaviour to classes that handling of a new data collection for MBI.
 * @since 2.2.0
 */
class ExportDataHandlerNotification implements ExportDataHandlerInterface
{
    /**
     * @var ExportDataHandler
     * @since 2.2.0
     */
    private $exportDataHandler;

    /**
     * @var Connector
     * @since 2.2.0
     */
    private $analyticsConnector;

    /**
     * ExportDataHandlerNotification constructor.
     *
     * @param ExportDataHandlerInterface $exportDataHandler
     * @param Connector $connector
     * @since 2.2.0
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
     * @since 2.2.0
     */
    public function prepareExportData()
    {
        $result = $this->exportDataHandler->prepareExportData();
        $this->analyticsConnector->execute('notifyDataChanged');
        return $result;
    }
}
