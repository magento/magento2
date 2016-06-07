<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Test\Block;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\ElementInterface;
use Magento\Mtf\Client\Locator;
use Magento\Mtf\Fixture\InjectableFixture;

class CreditCards extends Block
{
    /**
     * Delete button locator for popup window.
     *
     * @var string
     */
    private $deleteButton = './/*[@data-type="popup"]//span[text()="Delete"]';

    /**
     * Delete saved credit card.
     *
     * @param ElementInterface $creditCard
     */
    public function deleteCreditCard(ElementInterface $creditCard)
    {
        $creditCard->click();
        $this->browser->selectWindow();
        $this->browser->find($this->deleteButton, Locator::SELECTOR_XPATH)->click();
        $this->browser->selectWindow();
    }

    /**
     * Get saved credit cards on My Credit Cards page.
     *
     * @return array
     */
    public function getCreditCards()
    {
        $result = [];
        $elements = $this->_rootElement->getElements('./tbody/tr', Locator::SELECTOR_XPATH);
        foreach ($elements as $row) {
            $number = substr($row->find('./td[@data-th="Card Number"]', Locator::SELECTOR_XPATH)->getText(), -4, 4);
            $deleteButton = $row->find(
                "./td[text()[contains(.,'{$number}')]]/following-sibling::td[@data-th='Actions']//span[text()='Delete']",
                Locator::SELECTOR_XPATH
            );
            $result[$number] = $deleteButton;
        }
        return $result;
    }
}
