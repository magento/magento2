<?php
declare(strict_types=1);

namespace Tsg\WeatherWidget\Controller\Record;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Tsg\WeatherWidgetApi\Api\GetLastRecordInterface;

/**
 * Get last weather record controller.
 */
class Get implements HttpGetActionInterface
{
    private GetLastRecordInterface $getLastRecord;

    private ResultFactory $resultFactory;

    /**
     * @param GetLastRecordInterface $getLastRecord
     * @param ResultFactory $resultFactory
     */
    public function __construct(
        GetLastRecordInterface $getLastRecord,
        ResultFactory $resultFactory
    ) {
        $this->getLastRecord = $getLastRecord;
        $this->resultFactory = $resultFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $record = $this->getLastRecord->execute();


        if ($record->getRecordId()) {
            $resultJson->setHttpResponseCode(200);
            $resultJson->setData([
                'success' => true,
                'city' => $record->getCity(),
                'temperature' => $record->getTemperature(),
            ]);
        } else {
            $resultJson->setHttpResponseCode(404);
        }

        return $resultJson;
    }
}
