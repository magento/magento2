<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Observer;

use Magento\Csp\Model\Mode\ReportUriValidator;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Rejects CSP report URIs that cannot be rendered into the policy header.
 */
class ValidateReportUri implements ObserverInterface
{
    /**
     * Matches the area default and the per-action paths, e.g. csp/mode/storefront_checkout_index_index/report_uri
     */
    private const PATH_PATTERN = '#^csp/mode/[a-z0-9_]+/report_uri$#';

    /**
     * @var ReportUriValidator
     */
    private ReportUriValidator $reportUriValidator;

    /**
     * @param ReportUriValidator $reportUriValidator
     */
    public function __construct(ReportUriValidator $reportUriValidator)
    {
        $this->reportUriValidator = $reportUriValidator;
    }

    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $configValue = $observer->getEvent()->getData('config_data');
        if (!$configValue instanceof Value || !preg_match(self::PATH_PATTERN, (string)$configValue->getPath())) {
            return;
        }

        $value = (string)$configValue->getValue();
        if ($value !== '' && !$this->reportUriValidator->isValid($value)) {
            $field = $configValue->getFieldConfig();
            $label = is_array($field) && isset($field['label']) ? $field['label'] : 'Report URI';
            throw new LocalizedException(
                __('Invalid %1. Specify an absolute http(s) URL or a path starting with "/".', $label)
            );
        }
    }
}
