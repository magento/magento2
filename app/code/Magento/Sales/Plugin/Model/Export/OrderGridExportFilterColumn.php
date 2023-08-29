<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Plugin\Model\Export;

use Exception;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Sales\Model\ExportViewFilterProcessor;
use Magento\Ui\Model\Export\MetadataProvider;

/**
 * Process and filter order grid export columns according to view
 */
class OrderGridExportFilterColumn
{
    /**
     * @var ExportViewFilterProcessor
     */
    private $exportViewFilterProcessor;

    /**
     * @param ExportViewFilterProcessor $exportViewFilterProcessor
     */
    public function __construct(
        ExportViewFilterProcessor $exportViewFilterProcessor
    ) {
        $this->exportViewFilterProcessor = $exportViewFilterProcessor;
    }

    /**
     * Plugin which will check getHeaders and update headers according to the custom view
     *
     * @param MetadataProvider $subject
     * @param array $result
     * @param UiComponentInterface $component
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function afterGetHeaders(
        MetadataProvider $subject,
        array $result,
        UiComponentInterface $component
    ): array {
        $namespace = $component->getContext()->getNamespace();
        if ($namespace === 'sales_order_grid') {
            $activeColumns = $this->exportViewFilterProcessor->execute($component, $namespace, true);
            $result = !empty($activeColumns) ? $activeColumns : $result;
        }
        return $result;
    }

    /**
     * Plugin which will check getFields and update fields according to the custom view
     *
     * @param MetadataProvider $subject
     * @param array $result
     * @param UiComponentInterface $component
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function afterGetFields(
        MetadataProvider $subject,
        array $result,
        UiComponentInterface $component
    ): array {
        $namespace = $component->getContext()->getNamespace();
        if ($namespace === 'sales_order_grid') {
            $activeColumns = $this->exportViewFilterProcessor->execute($component, $namespace);
            $result = !empty($activeColumns) ? $activeColumns : $result;
        }
        return $result;
    }
}
