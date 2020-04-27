<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Ui\Component\Listing\Column\Guarantee;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Signifyd\Api\Data\CaseInterface;

/**
 * Class Options
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
 */
class Options implements OptionSourceInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => CaseInterface::GUARANTEE_DECLINED,
                'label' => $this->escaper->escapeHtml(__('Declined'))
            ],
            [
                'value' => CaseInterface::GUARANTEE_APPROVED,
                'label' => $this->escaper->escapeHtml(__('Approved'))
            ],
            [
                'value' => CaseInterface::GUARANTEE_CANCELED,
                'label' => $this->escaper->escapeHtml(__('Canceled'))
            ],
            [
                'value' => CaseInterface::GUARANTEE_PENDING,
                'label' => $this->escaper->escapeHtml(__('Pending'))
            ]
        ];
    }
}
