<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Invoice\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Class Options
 * @since 2.0.0
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var InvoiceRepositoryInterface
     * @since 2.0.0
     */
    protected $invoiceRepository;

    /**
     * Constructor
     *
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @since 2.0.0
     */
    public function __construct(InvoiceRepositoryInterface $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Get options
     *
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [];

            /** @var \Magento\Framework\Phrase $state */
            foreach ($this->invoiceRepository->create()->getStates() as $id => $state) {
                $this->options[] = [
                    'value' => $id,
                    'label' => $state->render()
                ];
            }
        }

        return $this->options;
    }
}
