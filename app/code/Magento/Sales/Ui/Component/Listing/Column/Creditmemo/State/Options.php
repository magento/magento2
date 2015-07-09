<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\CreditmemoFactory;

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
     * @var CreditmemoFactory
     */
    protected $creditmemoFactory;

    /**
     * Constructor
     *
     * @param CreditmemoFactory $creditmemoFactory
     */
    public function __construct(CreditmemoFactory $creditmemoFactory)
    {
        $this->creditmemoFactory = $creditmemoFactory;
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
            foreach ($this->creditmemoFactory->create()->getStates() as $id => $state) {
                $this->options[] = [
                    'value' => $id,
                    'label' => $state->render()
                ];
            }
        }

        return $this->options;
    }
}
