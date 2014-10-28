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
 * @spi
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Test\Block\Account;

use Magento\Customer\Test\Fixture\AddressInjectable;
use Mtf\Block\Block;
use Mtf\Client\Element\Locator;

/**
 * Class AddressesAdditional
 * Additional Addresses block
 */
class AddressesAdditional extends Block
{
    /**
     * Selector for address block
     *
     * @var string
     */
    protected $addressSelector = '//li[address[contains(.,"%s")]]';

    /**
     * Selector for delete link
     *
     * @var string
     */
    protected $deleteAddressLink = "[role='delete-address']";

    /**
     * Content of additional address block
     *
     * @var string
     */
    protected $additionalAddressContent = '.block-content';

    /**
     * Delete Additional Address
     *
     * @param AddressInjectable $address
     * @return void
     */
    public function deleteAdditionalAddress(AddressInjectable $address)
    {
        $this->_rootElement->find(sprintf($this->addressSelector, $address->getStreet()), Locator::SELECTOR_XPATH)
            ->find($this->deleteAddressLink)->click();
        $this->_rootElement->acceptAlert();
    }

    /**
     * Get block text
     *
     * @return string
     */
    public function getBlockText()
    {
        return $this->_rootElement->find($this->additionalAddressContent)->getText();
    }
}
