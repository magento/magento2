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
 * @category    Magento
 * @package     Magento_PageCache
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model;

use Magento\App\Request\Http;
use Magento\Stdlib\Cookie;

/**
 * Class Version
 * @package Magento\PageCache\Model
 */
class Version
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'private_content_version';

    /**
     * Ten years cookie period
     */
    const COOKIE_PERIOD = 315360000;

    /**
     * Cookie
     *
     * @var Cookie
     */
    private $cookie;

    /**
     * Request
     *
     * @var Http
     */
    private $request;

    /**
     * @param Cookie $cookie
     * @param Http $request
     */
    public function __construct(
        Cookie $cookie,
        Http $request
    ) {
        $this->cookie = $cookie;
        $this->request = $request;
    }

    /**
     * Increment private content version cookie (for user to pull new private content)
     *
     * @return void
     */
    private function set()
    {
        $this->cookie->set(self::COOKIE_NAME, $this->generateValue(), self::COOKIE_PERIOD, '/');
    }

    /**
     * Generate unique version identifier
     *
     * @return string
     */
    private function generateValue()
    {
        return md5(rand() . time());
    }

    /**
     * Handle private content version cookie
     * Set cookie if it is not set.
     * Increment version on post requests.
     * In all other cases do nothing.
     *
     * @return void
     */
    public function process()
    {
        if ($this->request->isPost()) {
                $this->set();
        }
    }
}
