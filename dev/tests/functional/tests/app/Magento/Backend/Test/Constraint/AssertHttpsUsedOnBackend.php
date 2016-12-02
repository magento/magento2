<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Backend\Test\Constraint;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Backend\Test\Page\Adminhtml\Dashboard;

/**
 * Assert that https protocol is used all over the Admin panel
 * It would be great if several different pages to validate are selected randomly in order to increase the coverage.
 * It would be great to assert somehow that browser console does not contain JS-related errors as well.
 */
class AssertHttpsUsedOnBackend extends AbstractConstraint
{
    /**
     * Secured protocol format.
     *
     * @var string
     */
    private $securedProtocol = 'https://';

    /**
     * Unsecured protocol format.
     *
     * @var string
     */
    private $unsecuredProtocol = 'http://';

    /**
     * Browser interface.
     *
     * @var BrowserInterface
     */
    protected $browser;

    /**
     * "Dashboard" page in Admin panel.
     *
     * @var Dashboard
     */
    protected $adminDashboardPage;

    /**
     * The list of Navigation Menu paths for Admin pages to verify.
     *
     * @var array
     */
    protected $pagesPaths;

    /**
     * Prepare data for further validations execution.
     *
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param BrowserInterface $browser
     * @param Dashboard $adminDashboardPage
     * @param string $severity
     * @param bool $active
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        BrowserInterface $browser,
        Dashboard $adminDashboardPage,
        $severity = 'low',
        $active = true
    ) {
        parent::__construct($objectManager, $eventManager, $severity, $active);
        $this->browser = $browser;
        $this->adminDashboardPage = $adminDashboardPage;
        $this->pagesPaths = ['Products>Catalog', 'Marketing>Catalog Price Rule'];
    }

    /**
     * Validations execution.
     *
     * @return void
     */
    public function processAssert()
    {
        // Open specified Admin pages using Navigation Menu to assert that JS is deployed validly as a part of statics.
        foreach ($this->pagesPaths as $pagePath) {
            $this->adminDashboardPage->open()->getMenuBlock()->navigate($pagePath);
            $this->assertUsedProtocol($this->securedProtocol);
            $this->assertDirectHttpUnavailable();
        }
    }

    /**
     * Assert that specified protocol is used on current page.
     *
     * @param string $expectedProtocol
     * @return void
     */
    protected function assertUsedProtocol($expectedProtocol)
    {
        \PHPUnit_Framework_Assert::assertStringStartsWith(
            $expectedProtocol,
            $this->browser->getUrl(),
            "$expectedProtocol is not used."
        );
    }

    /**
     *
     * Assert that Merchant is redirected to https if trying to access the page directly via http.
     *
     * @return void
     */
    protected function assertDirectHttpUnavailable()
    {
        $fakeUrl = str_replace($this->securedProtocol, $this->unsecuredProtocol, $this->browser->getUrl());
        $this->browser->open($fakeUrl);
        \PHPUnit_Framework_Assert::assertStringStartsWith(
            $this->securedProtocol,
            $this->browser->getUrl(),
            'Merchant is not redirected to https if tries to access the Admin panel page directly via http.'
        );

    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Unsecured URLs are used for Storefront pages.';
    }
}
