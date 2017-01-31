<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Controller\Adminhtml\Report\Customer;

use Magento\Reports\Controller\Adminhtml\Report\Customer\Accounts;
use Magento\Framework\DataObject;
use Magento\Framework\Phrase;

class AccountsTest extends \Magento\Reports\Test\Unit\Controller\Adminhtml\Report\AbstractControllerTest
{
    /**
     * @var \Magento\Reports\Controller\Adminhtml\Report\Customer\Accounts
     */
    protected $accounts;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->accounts = new Accounts(
            $this->contextMock,
            $this->fileFactoryMock
        );
    }

    /**
     * @return void
     */
    public function testExecute()
    {
        $titleMock = $this->getMockBuilder('Magento\Framework\View\Page\Title')
            ->disableOriginalConstructor()
            ->getMock();
        $titleMock
            ->expects($this->once())
            ->method('prepend')
            ->with(new Phrase('New Accounts Report'));

        $this->viewMock
            ->expects($this->any())
            ->method('getPage')
            ->willReturn(
                new DataObject(
                    ['config' => new DataObject(
                        ['title' => $titleMock]
                    )]
                )
            );

        $this->menuBlockMock
            ->expects($this->once())
            ->method('setActive')
            ->with('Magento_Reports::report_customers_accounts');
        $this->breadcrumbsBlockMock
            ->expects($this->at(0))
            ->method('addLink')
            ->with(new Phrase('Reports'), new Phrase('Reports'));
        $this->breadcrumbsBlockMock
            ->expects($this->at(1))
            ->method('addLink')
            ->with(new Phrase('Customers'), new Phrase('Customers'));
        $this->breadcrumbsBlockMock
            ->expects($this->at(2))
            ->method('addLink')
            ->with(new Phrase('New Accounts'), new Phrase('New Accounts'));
        $this->accounts->execute();
    }
}
