<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\ResourcesPopup;
use Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\TokensPopup;
use Mtf\Client\Element\Locator;

/**
 * Class IntegrationGrid
 * Integrations grid block
 */
class IntegrationGrid extends Grid
{
    /**
     * Initialize block elements
     *
     * @var array
     */
    protected $filters = [
        'name' => [
            'selector' => 'input[name="name"]',
        ],
        'status' => [
            'selector' => 'input[name="status"]',
            'input' => 'select',
        ],
    ];

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '[data-column="edit"] button';

    /**
     * Locator value for delete link
     *
     * @var string
     */
    protected $deleteLink = '[data-column="delete"] button';

    /**
     * Locator value for activate link
     *
     * @var string
     */
    protected $activateLink = '[data-column="activate"] a';

    /**
     * Selector for delete block confirmation window
     *
     * @var string
     */
    protected $deleteBlockSelector = './/ancestor::body/div[div[@id="integration-delete-container"]]';

    /**
     * Selector for Integration resources popup container
     *
     * @var string
     */
    protected $resourcesPopupSelector = './/ancestor::body/div[descendant::div[@id="integration-popup-container"]]';

    /**
     * Selector for Integration tokens popup container
     *
     * @var string
     */
    protected $tokensPopupSelector = './/ancestor::body/div[descendant::fieldset[contains(@id,"integration_token")]]';

    /**
     * Search and delete current item
     *
     * @param array $item
     * @return void
     */
    public function searchAndDelete(array $item)
    {
        $this->search($item);
        $this->_rootElement->find($this->deleteLink)->click();

        /** @var \Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\DeleteDialog $deleteDialog */
        $deleteDialog = $this->blockFactory->create(
            'Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\DeleteDialog',
            ['element' => $this->_rootElement->find($this->deleteBlockSelector, Locator::SELECTOR_XPATH)]
        );
        $deleteDialog->acceptDeletion();
    }

    /**
     * Search and activate current item
     *
     * @param array $filter
     * @return void
     */
    public function searchAndActivate(array $filter)
    {
        $this->search($filter);
        $this->_rootElement->find($this->activateLink)->click();
    }

    /**
     * Search and reauthorize current item
     *
     * @param array $filter
     * @return void
     */
    public function searchAndReauthorize(array $filter)
    {
        $this->search($filter);
        $this->_rootElement->find($this->activateLink)->click();
    }

    /**
     * Return Integration resources popup block
     *
     * @return ResourcesPopup
     */
    public function getResourcesPopup()
    {
        $resourcesPopup = $this->blockFactory->create(
            'Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\ResourcesPopup',
            ['element' => $this->_rootElement->find($this->resourcesPopupSelector, Locator::SELECTOR_XPATH)]
        );
        $this->waitForElementVisible($this->resourcesPopupSelector, Locator::SELECTOR_XPATH);

        return $resourcesPopup;
    }

    /**
     * Return Integration tokens popup block
     *
     * @return TokensPopup
     */
    public function getTokensPopup()
    {
        $tokensPopup = $this->blockFactory->create(
            'Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\TokensPopup',
            ['element' => $this->_rootElement->find($this->tokensPopupSelector, Locator::SELECTOR_XPATH)]
        );
        $this->waitForElementVisible($this->tokensPopupSelector, Locator::SELECTOR_XPATH);

        return $tokensPopup;
    }
}
