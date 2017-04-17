<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Test\Unit\DB\Logger;

use Magento\Framework\DB\Logger\LoggerProxy;
use Magento\Framework\DB\Logger\File;
use Magento\Framework\DB\Logger\Quiet;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class LoggerProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\DB\Logger\LoggerProxy
     */
    private $loggerProxy;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    /**
     * Test new logger proxy with file alias
     */
    public function testNewWithAliasFile()
    {
        $this->loggerProxy = $this->objectManager->getObject(
            LoggerProxy::class,
            [
                'loggerAlias' => LoggerProxy::LOGGER_ALIAS_FILE,
            ]
        );

        $this->assertInstanceOf(File::class, $this->loggerProxy->getLogger());
    }

    /**
     * Test new logger proxy with disabled alias
     */
    public function testNewWithAliasDisabled()
    {
        $this->loggerProxy = $this->objectManager->getObject(
            LoggerProxy::class,
            [
                'loggerAlias' => LoggerProxy::LOGGER_ALIAS_DISABLED,
            ]
        );

        $this->assertInstanceOf(Quiet::class, $this->loggerProxy->getLogger());
    }
}
