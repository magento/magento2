<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column\Creditmemo\State;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface;

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
     * @var CreditmemoRepositoryInterface
     * @since 2.0.0
     */
    protected $creditmemoRepository;

    /**
     * Constructor
     *
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @since 2.0.0
     */
    public function __construct(CreditmemoRepositoryInterface $creditmemoRepository)
    {
        $this->creditmemoRepository = $creditmemoRepository;
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
