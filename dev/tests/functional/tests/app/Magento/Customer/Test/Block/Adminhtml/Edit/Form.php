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

namespace Magento\Customer\Test\Block\Adminhtml\Edit;

use Mtf\Fixture\FixtureInterface;
use Magento\Backend\Test\Block\Widget\FormTabs;

/**
 * Class Form
 * Form for creation of the customer
 *
 */
class Form extends FormTabs
{
    /**
     * Fill Customer forms on tabs by customer, addresses data
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return $this
     */
    public function fillCustomer(FixtureInterface $customer, $address = null)
    {
        parent::fill($customer);

        if (null !== $address) {
            $this->openTab('addresses');
            $this->getTabElement('addresses')->fillAddresses($address);
        }

        return $this;
    }

    /**
     * Verify Customer information, addresses on tabs.
     *
     * @param FixtureInterface $customer
     * @param FixtureInterface|FixtureInterface[]|null $address
     * @return bool
     */
    public function verifyCustomer(FixtureInterface $customer, $address = null)
    {
        $isVerify = parent::verify($customer);

        if (null !== $address) {
            $this->openTab('addresses');
            $isVerify = $isVerify && $this->getTabElement('addresses')->verifyAddresses($address);
        }

        return $isVerify;
    }
}
