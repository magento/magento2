<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Ui\Component\Design\Config\SearchRobots;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Form\Field;

/**
 * ResetButton field instance
 *
 * @api
 * @since 100.1.9
 */
class ResetButton extends Field
{
    /**
     * Page robots default instructions
     */
    const XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS = 'design/search_engine_robots/default_custom_instructions';

    /**
     * ResetButton constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentInterface[] $components
     * @param array $data
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $components,
        array $data,
        private readonly ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Get robots.txt custom instruction default value
     *
     * @return string
     */
    private function getRobotsDefaultCustomInstructions()
    {
        return trim((string)$this->scopeConfig->getValue(
            self::XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ));
    }

    /**
     * Add js listener to reset button
     *
     * @return void
     * @throws LocalizedException
     * @since 100.1.9
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['actions'] = [
            [
                'actionName' => 'reset',
                'targetName' => '${ $.name }',
                '__disableTmpl' => ['targetName' => false],
                'params'     => [
                    json_encode($this->getRobotsDefaultCustomInstructions())
                ]
            ]
        ];
    }
}
