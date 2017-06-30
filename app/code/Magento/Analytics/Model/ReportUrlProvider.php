<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Connector\OTPRequest;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Provide URL on resource with reports.
 */
class ReportUrlProvider
{
    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     */
    private $analyticsToken;

    /**
     * Resource which provide OTP.
     *
     * @var OTPRequest
     */
    private $otpRequest;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * Path to config value with URL which provide reports.
     *
     * @var string
     */
    private $urlReportConfigPath = 'analytics/url/report';

    /**
     * @param AnalyticsToken $analyticsToken
     * @param OTPRequest $otpRequest
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        OTPRequest $otpRequest,
        ScopeConfigInterface $config
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->otpRequest = $otpRequest;
        $this->config = $config;
    }

    /**
     * Provide URL on resource with reports.
     *
     * @return string
     */
    public function getUrl()
    {
        $url = $this->config->getValue($this->urlReportConfigPath);
        if ($this->analyticsToken->isTokenExist()) {
            $otp = $this->otpRequest->call();
            if ($otp) {
                $query = http_build_query(['otp' => $otp], '', '&');
                $url .= '?' . $query;
            }
        }

        return $url;
    }
}
