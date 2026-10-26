<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Policy\Renderer;

use Magento\Csp\Api\Data\ModeConfiguredInterface;
use Magento\Csp\Api\Data\PolicyInterface;
use Magento\Csp\Api\ModeConfigManagerInterface;
use Magento\Csp\Api\PolicyRendererInterface;
use Magento\Csp\Model\Policy\SimplePolicyInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;

/**
 * Renders a simple policy as a "Content-Security-Policy" header.
 */
class SimplePolicyHeaderRenderer implements PolicyRendererInterface
{
    private const REPORTING_ENDPOINTS_HEADER = 'Reporting-Endpoints';

    private const REPORTING_GROUP = 'report-endpoint';

    /**
     * @var ModeConfigManagerInterface
     */
    private $modeConfig;

    /**
     * @param ModeConfigManagerInterface $modeConfig
     */
    public function __construct(ModeConfigManagerInterface $modeConfig)
    {
        $this->modeConfig = $modeConfig;
    }

    /**
     * @inheritDoc
     */
    public function render(PolicyInterface $policy, HttpResponse $response): void
    {
        /** @var SimplePolicyInterface $policy */
        $config = $this->modeConfig->getConfigured();
        if ($config->isReportOnly()) {
            $header = 'Content-Security-Policy-Report-Only';
        } else {
            $header = 'Content-Security-Policy';
        }
        $value = $policy->getId() .' ' .$policy->getValue() .';';
        if ($config->getReportUri() && !$response->getHeader(self::REPORTING_ENDPOINTS_HEADER)) {
            $value .= ' report-uri ' .$config->getReportUri() .';';
            $value .= ' report-to ' .self::REPORTING_GROUP .';';
            $endpoint = str_replace(['\\', '"'], ['\\\\', '\\"'], $config->getReportUri());
            $response->setHeader(
                self::REPORTING_ENDPOINTS_HEADER,
                self::REPORTING_GROUP .'="' .$endpoint .'"',
                true
            );
        }
        if ($existing = $response->getHeader($header)) {
            $value = $value .' ' .$existing->getFieldValue();
        }
        $response->setHeader($header, $value, true);
    }

    /**
     * @inheritDoc
     */
    public function canRender(PolicyInterface $policy): bool
    {
        return true;
    }
}
