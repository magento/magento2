<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Constraint;

use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Client\BrowserInterface;

/**
 * Assert no access to admin ui controller.
 *
 * @security-private
 */
class AssertAdminUrlNoAccess extends AbstractConstraint
{
    /**
     * Selector for page heading.
     *
     * @var string
     */
    private $heading = '.page-heading';

    /**
     * Urls to check.
     *
     * @var array
     */
    protected $urls = [
        'mui/index/render/?namespace=cms_block_listing&search=&filters%5Bplaceholder%5D=true'
        . '&paging%5BpageSize%5D=20&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=block_id'
        . '&sorting%5Bdirection%5D=asc&isAjax=true',
        'mui/index/render/?namespace=customer_listing&search=&filters%5Bplaceholder%5D=true'
        . '&paging%5BpageSize%5D=20&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=entity_id&sorting%5Bdirection%5D=asc'
        . '&isAjax=true',
        'mui/index/render/?namespace=cms_block_listing&search=&filters%5Bplaceholder%5D=true'
        . '&paging%5BpageSize%5D=20&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=block_id'
        . '&sorting%5Bdirection%5D=asc&isAjax=true',
        'mui/index/render/?namespace=cms_page_listing&search=&filters%5Bplaceholder%5D=true'
        . '&paging%5BpageSize%5D=20&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=page_id'
        . '&sorting%5Bdirection%5D=asc&isAjax=true',
        'mui/index/render/?namespace=product_listing&filters%5Bplaceholder%5D=true&paging%5BpageSize%5D=20'
        . '&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=entity_id&sorting%5Bdirection%5D=asc&isAjax=true',
        'mui/index/render/?namespace=sales_order_grid&search=&filters%5Bplaceholder%5D=true'
        . '&paging%5BpageSize%5D=20&paging%5Bcurrent%5D=1&sorting%5Bfield%5D=increment_id'
        . '&sorting%5Bdirection%5D=desc&isAjax=true'
    ];

    /**
     * Assert admin url no access.
     *
     * @param BrowserInterface $browser
     * @return void
     */
    public function processAssert(BrowserInterface $browser)
    {
        foreach ($this->urls as $url) {
            $browser->open($_ENV['app_backend_url'] . $url);
            \PHPUnit_Framework_Assert::assertTrue(
                strpos($browser->find($this->heading)->getText(), '404') !== false,
                'Admin UI controller access should require ACL permission.'
            );
            $browser->open($_ENV['app_backend_url']);
        }
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Admin UI controllers check ACL permission.';
    }
}
