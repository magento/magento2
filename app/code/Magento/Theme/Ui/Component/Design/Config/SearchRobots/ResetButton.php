<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Ui\Component\Design\Config\SearchRobots;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

/**
 * ResetButton field instance
 *
 * @api
 * @since 100.2.0
 */
class ResetButton extends Field
{
    /**
     * Page robots default instructions
     */
    const XML_PATH_ROBOTS_DEFAULT_CUSTOM_INSTRUCTIONS = 'design/search_engine_robots/default_custom_instructions';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ResetButton constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\View\Element\UiComponentInterface[] $components
     * @param array $data
     * @param ScopeConfigInterface $scopeConfig
     * @since 100.2.0
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        $components,
        array $data,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 100.2.0
     */
    public function prepare()
    {
        parent::prepare();

        $this->_data['config']['actions'] = [
            [
                'actionName' => 'reset',
                'targetName' => '${ $.name }',
                'params'     => [
                    json_encode($this->getRobotsDefaultCustomInstructions())
                ]
            ]
        ];
    }
}
