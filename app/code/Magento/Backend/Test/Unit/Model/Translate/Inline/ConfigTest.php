<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Model\Translate\Inline;

use Magento\Backend\App\ConfigInterface;
use Magento\Backend\Model\Translate\Inline\Config;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testIsActive()
    {
        $result = 'result';
        $backendConfig = $this->getMockForAbstractClass(ConfigInterface::class);
        $backendConfig->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            'dev/translate_inline/active_admin'
        )->willReturn(
            $result
        );
        $objectManager = new ObjectManager($this);
        $config = $objectManager->getObject(
            Config::class,
            ['config' => $backendConfig]
        );
        $this->assertEquals($result, $config->isActive('any'));
    }
}
