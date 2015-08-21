<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\MassAction\Group;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\Resource\Group\CollectionFactory;

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
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Additional options params
     *
     * @var array
     */
    protected $data;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(CollectionFactory $collectionFactory, array $data = [])
    {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $reference = isset($this->data['reference']) ? $this->data['reference'] : null;
        $paramName = isset($this->data['paramName']) ? $this->data['paramName'] : null;

        if ($this->options === null) {
            $options = $this->collectionFactory->create()->toOptionArray();
            foreach ($options as $optionCode) {
                $this->options[$reference][] = [
                    'type' => 'customer_group_' . $optionCode['value'],
                    'label' => $optionCode['label'],
                    'url' => isset($paramName) ? [$paramName => $optionCode['value']] : []
                ];
            }
        }
        return $this->options;
    }
}
