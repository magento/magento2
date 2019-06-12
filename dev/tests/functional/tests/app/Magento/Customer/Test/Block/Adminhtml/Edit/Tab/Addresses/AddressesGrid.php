<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab\Addresses;

use Magento\Ui\Test\Block\Adminhtml\DataGrid;

/**
 * Class AddressesGrid
 * Backend customer addresses grid
 *
 */
class AddressesGrid extends DataGrid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '//tr[@class="data-row"][1]//a[@data-action="item-edit"]';

    /**
     * First row selector
     *
     * @var string
     */
    protected $firstRowSelector = '//tr[@class="data-row"][1]';

    /**
     * Customer address grid loader.
     *
     * @var string
     */
    protected $loader = '.customer_form_areas_address_address_customer_address_listing [data-role="spinner"]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'firstname' => [
            'selector' => '.admin__data-grid-filters input[name*=firstname]',
        ],
        'lastname' => [
            'selector' => '.admin__data-grid-filters input[name*=lastname]',
        ],
        'street' => [
            'selector' => '.admin__data-grid-filters input[name*=street]',
        ],
        'city' => [
            'selector' => '.admin__data-grid-filters input[name*=city]',
        ],
        'region_id' => [
            'selector' => '.admin__data-grid-filters input[name*=region]',
        ],
        'postcode' => [
            'selector' => '.admin__data-grid-filters input[name*=postcode]',
        ],
        'telephone' => [
            'selector' => '.admin__data-grid-filters input[name*=telephone]',
        ],
        'country_id' => [
            'selector' => '.admin__data-grid-filters select[name*=country]',
            'input' => 'select',
        ],

    ];

    /**
     * Select action toggle.
     *
     * @var string
     */
    private $selectAction = '.action-select';

    /**
     * Delete action toggle.
     *
     * @var string
     */
    private $deleteAddress = '[data-action="item-delete"]';

    /**
     * Locator value for "Edit" link inside action column.
     *
     * @var string
     */
    private $editAddress = '[data-action="item-edit"]';

    /**
     * Customer address modal window.
     *
     * @var string
     */
    private $customerAddressModalForm = '.customer_form_areas_address_address_customer_address_update_modal';

    /**
     * Search customer address by filter.
     *
     * @param array $filter
     * @return void
     */
    public function search(array $filter): void
    {
        parent::search(array_intersect_key($filter, $this->filters));
    }

    /**
     * Delete customer address by filter
     *
     * @param array $filter
     * @return void
     * @throws \Exception
     */
    public function deleteCustomerAddress(array $filter): void
    {
        $this->search($filter);
        $rowItem = $this->getRow([$filter['firstname']]);
        if ($rowItem->isVisible()) {
            $this->deleteRowItemAddress($rowItem);
        } else {
            throw new \Exception("Searched item was not found by filter\n" . print_r($filter, true));
        }
    }

    /**
     * @param \Magento\Mtf\Client\Element\SimpleElement $rowItem
     * @return void
     */
    public function deleteRowItemAddress(\Magento\Mtf\Client\Element\SimpleElement $rowItem): void
    {
        $rowItem->find($this->selectAction)->click();
        $rowItem->find($this->deleteAddress)->click();
        $modalElement = $this->browser->find($this->confirmModal);
        /** @var \Magento\Ui\Test\Block\Adminhtml\Modal $modal */
        $modal = $this->blockFactory->create(
            \Magento\Ui\Test\Block\Adminhtml\Modal::class,
            ['element' => $modalElement]
        );
        $modal->acceptAlert();
        $this->waitLoader();
    }

    /**
     * Open first row from the addresses grid
     *
     * @return void
     */
    public function openFirstRow(): void
    {
        $firstRow = $this->getFirstRow();
        if ($firstRow->isVisible()) {
            $firstRow->find($this->selectAction)->click();
            $firstRow->find($this->editAddress)->click();
            $this->waitForElementVisible($this->customerAddressModalForm);
            $this->waitLoader();
        }
    }

    /**
     * Get first row from the grid
     *
     * @return \Magento\Mtf\Client\Element\SimpleElement
     */
    public function getFirstRow(): \Magento\Mtf\Client\Element\SimpleElement
    {
        return $this->_rootElement->find($this->firstRowSelector, \Magento\Mtf\Client\Locator::SELECTOR_XPATH);
    }
}
