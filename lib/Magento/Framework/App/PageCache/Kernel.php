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
namespace Magento\Framework\App\PageCache;

/**
 * Builtin cache processor
 */
class Kernel
{
    /**
     * @var Cache
     */
    protected $cache;

    /**
     * @var Identifier
     */
    protected $identifier;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param Cache $cache
     * @param Identifier $identifier
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\Framework\App\PageCache\Identifier $identifier,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->cache = $cache;
        $this->identifier = $identifier;
        $this->request = $request;
    }

    /**
     * Load response from cache
     *
     * @return \Magento\Framework\App\Response\Http|false
     */
    public function load()
    {
        if ($this->request->isGet() || $this->request->isHead()) {
            return unserialize($this->cache->load($this->identifier->getValue()));
        }
        return false;
    }

    /**
     * Modify and cache application response
     *
     * @param \Magento\Framework\App\Response\Http $response
     * @return void
     */
    public function process(\Magento\Framework\App\Response\Http $response)
    {
        if (preg_match('/public.*s-maxage=(\d+)/', $response->getHeader('Cache-Control')['value'], $matches)) {
            $maxAge = $matches[1];
            $response->setNoCacheHeaders();
            if ($response->getHttpResponseCode() == 200 && ($this->request->isGet() || $this->request->isHead())) {
                $tagsHeader = $response->getHeader('X-Magento-Tags');
                $tags = $tagsHeader ? explode(',', $tagsHeader['value']) : array();

                $response->clearHeader('Set-Cookie');
                $response->clearHeader('X-Magento-Tags');
                if (!headers_sent()) {
                    header_remove('Set-Cookie');
                }
                $this->cache->save(serialize($response), $this->identifier->getValue(), $tags, $maxAge);
            }
        }
    }
}
