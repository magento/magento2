<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filter;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Abstract class AbstractFilter
 */
abstract class AbstractFilter extends AbstractComponent
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param DataProvider $dataProvider
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        DataProvider $dataProvider,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->dataProvider = $dataProvider;
        $this->uiComponentFactory = $uiComponentFactory;
        parent::__construct($context, $components, $data);
    }
}
