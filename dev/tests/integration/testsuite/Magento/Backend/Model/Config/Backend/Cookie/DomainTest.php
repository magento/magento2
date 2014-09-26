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
namespace Magento\Backend\Model\Config\Backend\Cookie;

use Magento\Framework\Model\Exception;

/**
 * Test \Magento\Backend\Model\Config\Backend\Cookie\Domain
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
        /** @var $domain \Magento\Backend\Model\Config\Backend\Cookie\Domain */
        $domain = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Backend\Model\Config\Backend\Cookie\Domain'
        );
        $domain->setValue($value);
        try {
            $domain->save();
            if ($exceptionMessage ) {
                $this->fail('Failed to throw exception');
            } else {
                $this->assertNotNull($domain->getId());
            }
        } catch (Exception $e) {
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
                . 'The input does not appear to be a valid local network name'
            ],
            'valid hostname' => ['hostname.com'],
            'empty string' => [''],
        ];
    }
}
