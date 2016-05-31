<?php
/**
 * Store configuration edit form.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Config;

use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Block\BlockFactory;
use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Client\Locator;

/**
 * Class Form.
 */
class Form extends Block
{
    /**
     * Group block selector.
     *
     * @var string
     */
    protected $groupBlock = '.section-config.active #%s_%s';

    /**
     * Group block link selector.
     *
     * @var string
     */
    protected $groupBlockLink = '#%s_%s-head';

    /**
     * Save button selector.
     *
     * @var string
     */
    protected $saveButton = '#save';

    /**
     *  Tab content readiness.
     *
     * @var string
     */
    protected $tabReadiness = '.admin__page-nav-item._active._loading';

    /**
     *  Url associated with the form.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * @constructor
     * @param ElementInterface $element
     * @param BlockFactory $blockFactory
     * @param BrowserInterface $browser
     * @param array $config
     */
    public function __construct(
        ElementInterface $element,
        BlockFactory $blockFactory,
        BrowserInterface $browser,
        array $config = []
    ) {
        parent::__construct($element, $blockFactory, $browser, $config);
        $this->baseUrl = $this->browser->getUrl();
        if (substr($this->baseUrl, -1) !== '/') {
            $this->baseUrl = $this->baseUrl . '/';
        }
    }

    /**
     * Obtain store configuration form group.
     *
     * @param string $tabName
     * @param string $groupName
     * @return Form\Group
     */
    public function getGroup($tabName, $groupName)
    {
        $tabUrl = $this->baseUrl . 'section/' . $tabName;
        if ($this->getBrowserUrl() !== $tabUrl) {
            $this->browser->open($tabUrl);
        }
        $this->waitForElementNotVisible($this->tabReadiness);

        $groupElement = $this->_rootElement->find(
            sprintf($this->groupBlock, $tabName, $groupName),
            Locator::SELECTOR_CSS
        );

        if (!$groupElement->isVisible()) {
            $this->_rootElement->find(
                sprintf($this->groupBlockLink, $tabName, $groupName),
                Locator::SELECTOR_CSS
            )->click();

            $this->waitForElementNotVisible($this->tabReadiness);

            $groupElement = $this->_rootElement->find(
                sprintf($this->groupBlock, $tabName, $groupName),
                Locator::SELECTOR_CSS
            );
        }

        $blockFactory = Factory::getBlockFactory();
        return $blockFactory->getMagentoBackendSystemConfigFormGroup($groupElement);
    }

    /**
     * Retrieve url associated with the form.
     */
    public function getBrowserUrl()
    {
        return $this->browser->getUrl();
    }

    /**
     * Save store configuration.
     */
    public function save()
    {
        $this->_rootElement->find($this->saveButton, Locator::SELECTOR_CSS)->click();
    }
}
