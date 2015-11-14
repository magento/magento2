<?php

namespace Magento\Framework\Webapi\Exception;

class WebapiExceptionReport extends \Exception
{
    /**
     * @var string
     */
    private $reportId;

    /**
     * @param string     $reportId
     * @param \Exception $exception
     */
    public function __construct($reportId, \Exception $exception)
    {
        $this->reportId = $reportId;

        parent::__construct(
            "Report ID: {$reportId}; Message: {$exception->getMessage()}",
            $exception->getCode(),
            $exception
        );
    }

    /**
     * @return string
     */
    public function getReportId()
    {
        return $this->reportId;
    }
}
