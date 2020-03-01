<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Ups\Test\Unit\Model\Config\Backend;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ups\Model\Config\Backend\UpsUrl;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of UpsUrl backend type
 */
class UpsUrlTest extends TestCase
{

    /**
     * @var UpsUrl
     */
    private $config;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        /** @var UpsUrl $upsUrl */
        $this->config = $objectManager->getObject(UpsUrl::class);
    }

    /**
     * @dataProvider validDataProvider
     * @param string $data The valid data
     */
    public function testBeforeSave($data = null)
    {
        $this->config->setValue($data);
        $this->config->beforeSave();
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string $data The invalid data
     * @expectedException \Magento\Framework\Exception\ValidatorException
     * @expectedExceptionMessage UPS API endpoint URL's must use ups.com
     */
    public function testBeforeSaveErrors($data)
    {
        $this->config->setValue($data);
        $this->config->beforeSave();
    }

    public function validDataProvider()
    {
        return [
            [],
            [null],
            [''],
            ['http://ups.com'],
            ['https://foo.ups.com'],
            ['http://foo.ups.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }

    public function invalidDataProvider()
    {
        return [
            ['http://upsfoo.com'],
            ['https://fooups.com'],
            ['https://ups.com.fake.com'],
            ['https://ups.info'],
            ['http://ups.com.foo.com/foo/bar?baz=bash&fizz=buzz'],
            ['http://fooups.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }
}
