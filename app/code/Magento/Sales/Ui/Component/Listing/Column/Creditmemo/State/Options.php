<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

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
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * Constructor
     *
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     */
    public function __construct(CreditmemoRepositoryInterface $creditmemoRepository)
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
