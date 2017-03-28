<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Test\Unit\Model\Translate\Inline;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testIsActive()
    {
        $result = 'result';
        $backendConfig = $this->getMockForAbstractClass(\Magento\Backend\App\ConfigInterface::class);
        $backendConfig->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            $this->equalTo('dev/translate_inline/active_admin')
        )->will(
            $this->returnValue($result)
        );
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $config = $objectManager->getObject(
            \Magento\Backend\Model\Translate\Inline\Config::class,
            ['config' => $backendConfig]
        );
        $this->assertEquals($result, $config->isActive('any'));
    }
}
