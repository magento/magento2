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

namespace Magento\GiftMessage\Test\Block\Adminhtml\Order\Create;

use Mtf\Block\Form as ParentForm;
use Mtf\Fixture\FixtureInterface;
use Mtf\Client\Element;

/**
 * Class Form
 * Backend item form for gift message
 */
class Form extends ParentForm
{
    /**
     * Selector for 'OK' button.
     *
     * @var string
     */
    protected $okButton = '#gift_options_ok_button';

    /**
     * Fill backend GiftMessage item form.
     *
     * @param FixtureInterface $fixture
     * @param Element|null $element
     * @return $this
     */
    public function fill(FixtureInterface $fixture, Element $element = null)
    {
        parent::fill($fixture, $element);
        $this->_rootElement->find($this->okButton)->click();
    }
}
