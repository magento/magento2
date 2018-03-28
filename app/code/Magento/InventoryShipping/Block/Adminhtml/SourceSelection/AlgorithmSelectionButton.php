<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Block\Adminhtml\SourceSelection;

use Magento\Backend\Block\Widget\Button\SplitButton;
use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\InventorySourceSelectionApi\Api\GetSourceSelectionAlgorithmListInterface;
use Magento\Framework\Registry;

/**
 * Class AlgorithmSelectionButton
 *
 * @api
 */
class AlgorithmSelectionButton extends Container
{
    /**
     * @var GetSourceSelectionAlgorithmListInterface
     */
    private $getSourceSelectionAlgorithmList;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param Context $context
     * @param GetSourceSelectionAlgorithmListInterface $getSourceSelectionAlgorithmList
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        GetSourceSelectionAlgorithmListInterface $getSourceSelectionAlgorithmList,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getSourceSelectionAlgorithmList = $getSourceSelectionAlgorithmList;
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        //TODO: Add priority algorithm as default
        if (!empty($this->getAlgorithmsListOptions())) {
            $addButtonProps = [
                'id' => 'algorithm_action_list',
                'label' => __('Source Selection Algorithm'),
                'class' => 'add',
                'button_class' => '',
                'class_name' => SplitButton::class,
                'options' => $this->getAlgorithmsListOptions(),
            ];

            $this->buttonList->add('algorithm_action_list', $addButtonProps);
        }
        return parent::_prepareLayout();
    }

    /**
     * Retrieve options for 'AlgorithmActionList' split button
     *
     * @return array
     */
    protected function getAlgorithmsListOptions()
    {
        $algorithmsList = $this->getSourceSelectionAlgorithmList->execute();
        $splitButtonOptions = [];
        foreach ($algorithmsList as $algorithm) {
            $splitButtonOptions[$algorithm->getCode()] = [
                'label' => $algorithm->getTitle(),
                'onclick' => 'processAlgorithm("'.$algorithm->getCode().'")'
            ];
        }
        return $splitButtonOptions;
    }
}
