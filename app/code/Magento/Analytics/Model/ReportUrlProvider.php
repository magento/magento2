<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Model;

use Magento\Analytics\Model\Config\Backend\Baseurl\SubscriptionUpdateHandler;
use Magento\Analytics\Model\Connector\OTPRequest;
use Magento\Analytics\Model\Exception\State\SubscriptionUpdateException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\FlagManager;

/**
 * Provide URL on resource with reports.
 * @since 2.2.0
 */
class ReportUrlProvider
{
    /**
     * Resource for handling MBI token value.
     *
     * @var AnalyticsToken
     * @since 2.2.0
     */
    private $analyticsToken;

    /**
     * Resource which provide OTP.
     *
     * @var OTPRequest
     * @since 2.2.0
     */
    private $otpRequest;

    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $config;

    /**
     * @var FlagManager
     * @since 2.2.0
     */
    private $flagManager;

    /**
     * Path to config value with URL which provide reports.
     *
     * @var string
     * @since 2.2.0
     */
    private $urlReportConfigPath = 'analytics/url/report';

    /**
     * @param AnalyticsToken $analyticsToken
     * @param OTPRequest $otpRequest
     * @param ScopeConfigInterface $config
     * @param FlagManager $flagManager
     * @since 2.2.0
     */
    public function __construct(
        AnalyticsToken $analyticsToken,
        OTPRequest $otpRequest,
        ScopeConfigInterface $config,
        FlagManager $flagManager
    ) {
        $this->analyticsToken = $analyticsToken;
        $this->otpRequest = $otpRequest;
        $this->config = $config;
        $this->flagManager = $flagManager;
    }

    /**
     * Provide URL on resource with reports.
     *
     * @return string
     * @throws SubscriptionUpdateException
     * @since 2.2.0
     */
    public function getUrl()
    {
        if ($this->flagManager->getFlagData(SubscriptionUpdateHandler::PREVIOUS_BASE_URL_FLAG_CODE)) {
            throw new SubscriptionUpdateException(__(
                'Your Base URL has been changed and your reports are being updated. '
                . 'Advanced Reporting will be available once this change has been processed. Please try again later.'
            ));
        }

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
