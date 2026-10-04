<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Model\Export;

class MandatoryAttributesProvider
{
    /**
     * @var array
     */
    private $mandatoryEavAttributes;

    /**
     * @var array
     */
    private $systemColumnProviders;

    /**
     * @param array $mandatoryEavAttributes
     * @param array $systemColumnProviders Array of objects that append export headers
     */
    public function __construct(
        array $mandatoryEavAttributes = [],
        array $systemColumnProviders = []
    ) {
        $this->mandatoryEavAttributes = $mandatoryEavAttributes;
        $this->systemColumnProviders = $systemColumnProviders;
    }

    /**
     * Returns explicitly defined EAV attributes (e.g., sku)
     */
    public function getMandatoryEavAttributes(): array
    {
        return $this->mandatoryEavAttributes;
    }

    /**
     * Dynamically triggers injected providers to extract appended system columns
     */
    public function getMandatorySystemAttributes(): array
    {
        $headersList = [];

        foreach ($this->systemColumnProviders as $provider) {
            if (is_object($provider) && is_callable([$provider, 'addHeaderColumns'])) {
                $providerHeaders = $provider->addHeaderColumns([]);
                if (is_array($providerHeaders) && !empty($providerHeaders)) {
                    $headersList[] = $providerHeaders;
                }
            }
        }

        if (empty($headersList)) {
            return [];
        }

        // Merge all collected arrays at once using the spread operator
        $headers = array_merge(...$headersList);

        return array_values($headers);
    }
}
