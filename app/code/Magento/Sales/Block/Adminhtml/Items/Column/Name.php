<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Items\Column;

use Magento\Framework\Filter\TruncateFilter\Result;

/**
 * Sales Order items name column renderer
 *
 * @api
 * @since 100.0.2
 */
class Name extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{
    /**
     * @var Result
     */
    private $truncateResult = null;

    /**
     * Truncate string
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string &$remainder
     * @param bool $breakWords
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function truncateString(
        string $value,
        int $length = 80,
        string $etc = '...',
        string &$remainder = '',
        bool $breakWords = true
    ): string {
        $this->truncateResult = $this->filterManager->truncateFilter(
            $value,
            ['length' => $length, 'etc' => $etc, 'breakWords' => $breakWords]
        );
        return $this->truncateResult->getValue();
    }

    /**
     * Add line breaks and truncate value
     *
     * @param string $value
     * @return array
     */
    public function getFormattedOption(string $value): array
    {
        $remainder = '';
        $this->truncateString($value, 55, '', $remainder);
        $result = [
            'value' => nl2br($this->truncateResult->getValue()),
            'remainder' => nl2br($this->truncateResult->getRemainder())
        ];

        return $result;
    }
}
