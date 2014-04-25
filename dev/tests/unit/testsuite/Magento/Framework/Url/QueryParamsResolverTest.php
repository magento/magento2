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

namespace Magento\Framework\Url;

use Magento\TestFramework\Helper\ObjectManager;

class QueryParamsResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\Url\QueryParamsResolver */
    protected $object;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject('Magento\Framework\Url\QueryParamsResolver');
    }

    public function testGetQuery()
    {
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals('foo=bar&true=false', $this->object->getQuery());
    }

    public function testGetQueryEscaped()
    {
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals('foo=bar&amp;true=false', $this->object->getQuery(true));
    }

    public function testSetQuery()
    {
        $this->object->setQuery('foo=bar&true=false');
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testSetQueryIdempotent()
    {
        $this->object->setQuery(null);
        $this->assertEquals([], $this->object->getQueryParams());
    }

    public function testSetQueryParam()
    {
        $this->object->setQueryParam('foo', 'bar');
        $this->object->setQueryParam('true', 'false');
        $this->object->setQueryParam('foo', 'bar');
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testSetQueryParams()
    {
        $this->object->setQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }

    public function testAddQueryParamsIdempotent()
    {
        $this->object->setData('query_params', ['foo' => 'bar', 'true' => 'false']);
        $this->object->addQueryParams(['foo' => 'bar', 'true' => 'false']);
        $this->assertEquals(['foo' => 'bar', 'true' => 'false'], $this->object->getQueryParams());
    }
}
