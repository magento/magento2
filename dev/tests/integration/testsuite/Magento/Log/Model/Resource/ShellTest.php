<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Log\Model\Resource;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Log\Model\Resource\Shell
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Log\Model\Resource\Shell'
        );
    }

    public function testGetTablesInfo()
    {
        $tables = $this->_model->getTablesInfo();
        $this->assertNotEmpty($tables);

        $sample = current($tables);
        $requiredKeys = ['name', 'rows', 'data_length', 'index_length'];
        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $sample);
        }
    }
}
