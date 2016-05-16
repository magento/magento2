<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Variable\Model\Variable;

/**
 * @magentoAppArea adminhtml
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Variable\Model\Variable\Config
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Variable\Model\Variable\Config'
        );
    }

    public function testGetWysiwygJsPluginSrc()
    {
        $src = $this->_model->getWysiwygJsPluginSrc();
        $this->assertStringStartsWith('http://localhost/pub/static/adminhtml/Magento/backend/en_US/mage/adminhtml/',
            $src);
        $this->assertStringEndsWith('editor_plugin.js', $src);
    }
}
