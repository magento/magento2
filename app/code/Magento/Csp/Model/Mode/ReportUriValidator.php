<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Mode;

use Magento\Framework\Validator\Url as UrlValidator;

/**
 * Checks that a configured value can be rendered as the report-uri directive of a CSP header.
 */
class ReportUriValidator
{
    /**
     * @var UrlValidator
     */
    private UrlValidator $urlValidator;

    /**
     * @param UrlValidator $urlValidator
     */
    public function __construct(UrlValidator $urlValidator)
    {
        $this->urlValidator = $urlValidator;
    }

    /**
     * Accept an absolute http(s) URL or a path starting with "/".
     *
     * @param string $reportUri
     * @return bool
     */
    public function isValid(string $reportUri): bool
    {
        // Whitespace, control characters, ";" and "," would end the directive or the policy inside the header.
        if ($reportUri === '' || preg_match('/[\x00-\x20\x7F;,]/', $reportUri)) {
            return false;
        }

        if (str_starts_with($reportUri, '/')) {
            return true;
        }

        return $this->urlValidator->isValid($reportUri, ['http', 'https']);
    }
}
