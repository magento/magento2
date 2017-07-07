<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class CrontabConfigFilesTest extends AbstractMergedConfigTest
{
    /**
     * attributes represent merging rules
     * copied from original class \Magento\Framework\App\Route\Config\Reader
     *
     * @var array
     */
    protected function getIdAttributes()
    {
        return ['/config/group' => 'id', '/config/group/job' => 'name'];
    }

    /**
     * Path to tough XSD for merged file validation
     *
     * @var string
     */
    protected function getMergedSchemaFile()
    {

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        return $objectManager->get(\Magento\Cron\Model\Config\SchemaLocator::class)->getSchema();
    }

    protected function getConfigFiles()
    {
        return \Magento\Framework\App\Utility\Files::init()->getConfigFiles('crontab.xml');
    }
}
