<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Backend;

/**
 * @magentoAppArea adminhtml
 */
class BaseurlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $path
     * @param string $value
     * @magentoDbIsolation enabled
     * @dataProvider validationDataProvider
     */
    public function testValidation($path, $value)
    {
        /** @var $model \Magento\Config\Model\Config\Backend\Baseurl */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\Config\Backend\Baseurl::class
        );
        $model->setPath($path)->setValue($value)->save();
        $this->assertNotEmpty((int)$model->getId());
    }

    /**
     * @return array
     */
    public function validationDataProvider()
    {
        $basePlaceholder = '{{base_url}}';
        $unsecurePlaceholder = '{{unsecure_base_url}}';
        $unsecureSuffix = '{{unsecure_base_url}}test/';
        $securePlaceholder = '{{secure_base_url}}';
        $secureSuffix = '{{secure_base_url}}test/';

        return [
            ['any/path', 'http://example.com/'],
            ['any/path', 'http://example.com/uri/'],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, $basePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $basePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $securePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $secureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $securePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $secureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $securePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $secureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $unsecureSuffix],
        ];
    }

    /**
     * @param string $path
     * @param string $value
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider validationExceptionDataProvider
     */
    public function testValidationException($path, $value)
    {
        /** @var $model \Magento\Config\Model\Config\Backend\Baseurl */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\Config\Backend\Baseurl::class
        );
        $model->setPath($path)->setValue($value)->save();
    }

    /**
     * @return array
     */
    public function validationExceptionDataProvider()
    {
        $baseSuffix = '{{base_url}}test/';
        $unsecurePlaceholder = '{{unsecure_base_url}}';
        $unsecureSuffix = '{{unsecure_base_url}}test/';
        $unsecureWrongSuffix = '{{unsecure_base_url}}test';
        $unsecureWrongDomainName = 'http://example.com_test/';
        $securePlaceholder = '{{secure_base_url}}';
        $secureSuffix = '{{secure_base_url}}test/';
        $secureWrongSuffix = '{{secure_base_url}}test';
        $secureWrongDomainName = 'https://example.com_test/';

        return [
            ['', 'not a valid URL'],
            ['', 'example.com'],
            ['', 'http://example.com'],
            ['', 'http://example.com/uri'],
            ['', $unsecureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, $baseSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, $unsecureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, $unsecurePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL, $unsecureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, $baseSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_LINK_URL, $unsecureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_MEDIA_URL, $unsecureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL, $unsecureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_STATIC_URL, $unsecureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $baseSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $secureSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $securePlaceholder],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_URL, $secureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, ''],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $baseSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $secureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_LINK_URL, $secureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $secureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_MEDIA_URL, $secureWrongDomainName],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $secureWrongSuffix],
            [\Magento\Store\Model\Store::XML_PATH_SECURE_BASE_STATIC_URL, $secureWrongDomainName],
        ];
    }
}
