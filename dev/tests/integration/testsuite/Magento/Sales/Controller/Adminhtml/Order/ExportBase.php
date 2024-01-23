<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\App\Request\Http;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Tests for order/invoice/shipment/credit memo export via admin grids.
 *
 * @magentoDbIsolation disabled
 */
class ExportBase extends AbstractBackendController
{
    const CSV_FORMAT = 'csv';
    const XML_FORMAT = 'xml';

    /**
     * @var OrderInterfaceFactory
     */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * Dispatches export request.
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    protected function dispatchExport(string $url, array $params): string
    {
        $this->_auth->getAuthStorage()->setIsFirstPageAfterLogin(false);
        $this->getRequest()->setParams($params);
        $this->getRequest()->setMethod(Http::METHOD_POST);
        ob_start();
        $this->dispatch($url);

        return ob_get_clean();
    }

    /**
     * Parses string response depends of format.
     *
     * @param string $format
     * @param string $response
     * @return array
     */
    protected function parseResponse(string $format, string $response): array
    {
        $result = [];
        if ($format === ExportBase::CSV_FORMAT) {
            $result = $this->parseCsvResponse($response);
        } elseif ($format === ExportBase::XML_FORMAT) {
            $result = $this->parseXmlResponse($response);
        }

        return $result;
    }

    /**
     * Converts string in scv format to assoc array.
     *
     * @param string $data
     * @return array
     */
    protected function parseCsvResponse(string $data): array
    {
        $result = [];
        $data = str_getcsv($data, PHP_EOL);
        $headers = str_getcsv(array_shift($data), ',', '"');
        foreach ($data as $row) {
            $result[] = array_combine($headers, str_getcsv($row, ',', '"'));
        }

        return $result;
    }

    /**
     * Converts string in xml format to assoc array.
     *
     * @param string $data
     * @return array
     */
    protected function parseXmlResponse(string $data): array
    {
        $xml = simplexml_load_string($data);
        $xmlAsArray = [];
        foreach ($xml->Worksheet->Table->Row as $item) {
            $row = [];
            foreach ($item->Cell as $cell) {
                $data = (array)$cell->Data;
                $row[] = reset($data);
            }
            $xmlAsArray[] = $row;
        }
        $result = [];
        $headers = array_shift($xmlAsArray);
        foreach ($xmlAsArray as $row) {
            $result[] = array_combine($headers, $row);
        }

        return $result;
    }

    /**
     * Returns order purchase date in timezone.
     *
     * @param string $date
     * @param string $timezone
     * @return string
     */
    protected function prepareDate(string $date, string $timezone): string
    {
        $date = new \DateTime($date, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone($timezone));

        return $date->format('M j, Y h:i:s A');
    }

    /**
     * Returns order by increment id.
     *
     * @param string $incrementId
     * @return OrderInterface
     */
    protected function getOrder(string $incrementId): OrderInterface
    {
        return $this->orderFactory->create()->loadByIncrementId($incrementId);
    }

    /**
     * Returns export url.
     *
     * @param string $format
     * @param int|null $orderId
     * @return string
     */
    protected function getExportUrl(string $format, ?int $orderId = null): string
    {
        $url = $format === self::CSV_FORMAT
            ? 'backend/mui/export/gridToCsv/'
            : 'backend/mui/export/gridToXml/';

        return $orderId ? $url . 'order_id/' . $orderId : $url;
    }
}
