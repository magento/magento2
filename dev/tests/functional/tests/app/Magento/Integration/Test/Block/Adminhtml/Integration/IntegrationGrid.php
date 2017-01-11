<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Test\Block\Adminhtml\Integration;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\ResourcesPopup;
use Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\TokensPopup;
use Magento\Mtf\Client\Locator;

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
     * Selector for confirm.
     *
     * @var string
     */
    protected $confirmModal = '.confirm._show[data-role=modal]';

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
        $element = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(\Magento\Ui\Test\Block\Adminhtml\Modal::class, ['element' => $element]);
        $modal->acceptAlert();
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
        /** @var ResourcesPopup $resourcesPopup */
        $resourcesPopup = $this->blockFactory->create(
            \Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\ResourcesPopup::class,
            ['element' => $this->_rootElement->find($this->resourcesPopupSelector, Locator::SELECTOR_XPATH)]
        );

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
            \Magento\Integration\Test\Block\Adminhtml\Integration\IntegrationGrid\TokensPopup::class,
            ['element' => $this->_rootElement->find($this->tokensPopupSelector, Locator::SELECTOR_XPATH)]
        );
        $this->waitForElementVisible($this->tokensPopupSelector, Locator::SELECTOR_XPATH);

        return $tokensPopup;
    }
}
