<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cookie\Model\Config\Backend;

use Magento\Framework\Exception\LocalizedException;

/**
 * Test \Magento\Cookie\Model\Config\Backend\Domain
 *
 * @magentoAppArea adminhtml
 */
class DomainTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $value
     * @param string $exceptionMessage
     * @magentoDbIsolation enabled
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave($value, $exceptionMessage = null)
    {
        /** @var $domain \Magento\Cookie\Model\Config\Backend\Domain */
        $domain = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Cookie\Model\Config\Backend\Domain::class
        );
        $domain->setValue($value);
        $domain->setPath('path');
        try {
            $domain->save();
            if ($exceptionMessage) {
                $this->fail('Failed to throw exception');
            } else {
                $this->assertNotNull($domain->getId());
            }
        } catch (LocalizedException $e) {
            $this->assertContains('Invalid domain name: ', $e->getMessage());
            $this->assertEquals($exceptionMessage, $e->getMessage());
            $this->assertNull($domain->getId());
        }
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            'not string' => [['array'], 'Invalid domain name: must be a string'],
            'invalid hostname' => [
                'http://',
                'Invalid domain name: The input does not match the expected structure for a DNS hostname; '
                . 'The input does not appear to be a valid URI hostname; '
                . 'The input does not appear to be a valid local network name',
            ],
            'valid hostname' => ['hostname.com'],
            'empty string' => [''],
        ];
    }
}
