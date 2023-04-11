<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Fedex\Test\Unit\Model\Config\Backend;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Fedex\Model\Config\Backend\FedexUrl;
use PHPUnit\Framework\TestCase;

/**
 * Verify behavior of FedexUrl backend type
 */
class FedexUrlTest extends TestCase
{

    /**
     * @var FedexUrl
     */
    private $config;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        /** @var FedexUrl $fedexUrl */
        $this->config = $objectManager->getObject(FedexUrl::class);
    }

    /**
     * @dataProvider validDataProvider
     * @param string|null $data The valid data
     * @throws ValidatorException
     */
    public function testBeforeSave(string $data = null)
    {
        $this->config->setValue($data);
        $this->config->beforeSave();
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string $data The invalid data
     */
    public function testBeforeSaveErrors(string $data)
    {
        $this->expectException('Magento\Framework\Exception\ValidatorException');
        $this->expectExceptionMessage('Fedex API endpoint URL\'s must use fedex.com');
        $this->config->setValue($data);
        $this->config->beforeSave();
    }

    /**
     * Validator Data Provider
     *
     * @return array
     */
    public function validDataProvider(): array
    {
        return [
            [],
            [null],
            [''],
            ['http://fedex.com'],
            ['https://foo.fedex.com'],
            ['http://foo.fedex.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }

    /**
     * @return \string[][]
     */
    public function invalidDataProvider(): array
    {
        return [
            ['http://fedexfoo.com'],
            ['https://foofedex.com'],
            ['https://fedex.com.fake.com'],
            ['https://fedex.info'],
            ['http://fedex.com.foo.com/foo/bar?baz=bash&fizz=buzz'],
            ['http://foofedex.com/foo/bar?baz=bash&fizz=buzz'],
        ];
    }
}
