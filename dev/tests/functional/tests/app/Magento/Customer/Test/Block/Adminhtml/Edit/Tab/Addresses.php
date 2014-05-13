<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\Block\Adminhtml\Edit\Tab;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\Widget\Tab;

/**
 * Class Addresses
 * Customer addresses edit block
 *
 */
class Addresses extends Tab
{
    /**
     * "Add New Customer" button
     *
     * @var string
     */
    protected $addNewAddress = '#add_address_button';

    /**
     * Open customer address
     *
     * @var string
     */
    protected $customerAddress = '//*[@id="address_list"]/li[%d]/a';

    /**
     * Fill customer addresses
     *
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function fillAddresses($address)
    {
        if (null !== $address) {
            $addresses = is_array($address) ? $address : [$address];

            foreach ($addresses as $address) {
                if ($address->hasData()) {
                    $this->addNewAddress();
                    $this->fillFormTab($address->getData(), $this->_rootElement);
                }
            }
        }

        return $this;
    }

    /**
     * Verify customer addresses
     *
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return bool
     */
    public function verifyAddresses($address)
    {
        $addresses = is_array($address) ? $address : [1 => $address];

        foreach ($addresses as $addressNumber => $address) {
            if ($address->hasData()) {
                $this->openCustomerAddress($addressNumber);
                if (!$this->verifyFormTab($address->getData(), $this->_rootElement)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Click "Add New Address" button
     */
    protected function addNewAddress()
    {
        $this->_rootElement->find($this->addNewAddress)->click();
    }

    /**
     * Open customer address
     *
     * @param int $addressNumber
     * @throws \Exception
     */
    protected function openCustomerAddress($addressNumber)
    {
        $addressTab = $this->_rootElement->find(
            sprintf($this->customerAddress, $addressNumber),
            Locator::SELECTOR_XPATH
        );

        if (!$addressTab->isVisible()) {
            throw new \Exception("Can't open customer address #{$addressNumber}");
        }
        $addressTab->click();
    }
}
