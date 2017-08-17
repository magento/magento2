<?php
/**
 * Store configuration edit form.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Block\System\Config;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;

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
     * Obtain store configuration form group.
     *
     * @param string $tabName
     * @param string $groupName
     * @return Form\Group
     */
    public function getGroup($tabName, $groupName)
    {
        $this->baseUrl = $this->getBrowserUrl();
        if (substr($this->baseUrl, -1) !== '/') {
            $this->baseUrl = $this->baseUrl . '/';
        }

        $tabUrl = $this->getTabUrl($tabName);

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

    /**
     * Checks whether secret key is presented in base url and returns menu tab url.
     *
     * @param string $tabName
     * @return string
     */
    private function getTabUrl($tabName)
    {
        $tabIndex = 'index/section/' . $tabName;
        if (strpos($this->baseUrl, $tabIndex) !== false) {
            return $this->baseUrl;
        }
        if (strpos($this->baseUrl, '/key/') !== false) {
            /*
             * Slashes are concatenated to cover case when string 'index' presented in domain name
             * or somewhere else in url additionally.
             */
            $tabUrl =  str_replace('/index/', '/' . $tabIndex . '/', $this->baseUrl);
        } elseif (strpos($this->baseUrl, '/edit/') !== false) {
            $tabUrl =  str_replace('/edit/', '/' . $tabIndex . '/', $this->baseUrl);
        } else {
            $tabUrl = $this->baseUrl . $tabIndex;
        }

        return $tabUrl;
    }
}
