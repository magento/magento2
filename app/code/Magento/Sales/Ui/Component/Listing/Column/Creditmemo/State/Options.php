<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\CreditmemoRepository;

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
     * @var CreditmemoRepository
     */
    protected $creditmemoRepository;

    /**
     * Constructor
     *
     * @param CreditmemoRepository $creditmemoRepository
     */
    public function __construct(CreditmemoRepository $creditmemoRepository)
    {
        $this->creditmemoRepository = $creditmemoRepository;
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
            foreach ($this->creditmemoRepository->create()->getStates() as $id => $state) {
                $this->options[] = [
                    'value' => $id,
                    'label' => $state->render()
                ];
            }
        }

        return $this->options;
    }
}
