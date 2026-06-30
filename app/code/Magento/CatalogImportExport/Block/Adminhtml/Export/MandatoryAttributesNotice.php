<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Block\Adminhtml\Export;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\ImportExport\Model\Export\MandatoryAttributesProvider;

/**
 * Renders a notice listing mandatory (non-excludable) export columns.
 */
class MandatoryAttributesNotice extends Template
{
    /**
     * @var MandatoryAttributesProvider
     */
    private MandatoryAttributesProvider $mandatoryAttributesProvider;

    /**
     * @param Context $context
     * @param MandatoryAttributesProvider $mandatoryAttributesProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        MandatoryAttributesProvider $mandatoryAttributesProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->mandatoryAttributesProvider = $mandatoryAttributesProvider;
    }

    /**
     * Returns all mandatory attributes (EAV and system) as a comma-separated string.
     *
     * @return string
     */
    public function getSystemMandatoryAttributes(): string
    {
        $attributes = array_merge(
            $this->mandatoryAttributesProvider->getMandatoryEavAttributes(),
            $this->mandatoryAttributesProvider->getMandatorySystemAttributes()
        );

        return implode(', ', $attributes);
    }
}