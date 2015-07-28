<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Invoice\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\InvoiceRepository;

/**
 * Class Options
 */
class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * Constructor
     *
     * @param InvoiceRepository $invoiceRepository
     */
    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Get options
     *
     * @return array
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
