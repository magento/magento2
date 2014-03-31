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
namespace Magento\Indexer\Model;

class ShellTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns prepared model
     *
     * @param string $entryPoint
     * @return \Magento\Indexer\Model\Shell
     */
    protected function getModel($entryPoint = 'fake.php')
    {
        return \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Indexer\Model\Shell',
            array('entryPoint' => $entryPoint)
        );
    }

    /**
     * Returns result of running model - can be real model or mocked one
     *
     * @param \Magento\Indexer\Model\Shell $model Can be mock
     * @return string
     */
    protected function runModel($model)
    {
        ob_start();
        $model->run();
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }

    public function testGetUsageHelp()
    {
        $model = $this->getModel('testme.php');
        $this->assertContains('testme.php', $model->getUsageHelp());
    }

    public function testRunWithoutParams()
    {
        $model = $this->getModel('testme.php');
        $result = $this->runModel($model);
        $this->assertContains('testme.php', $result);
        $this->assertContains('index', $result); // Something about indexes
    }

    public function testRunIndexList()
    {
        $model = $this->getModel('testme.php');
        $model->setRawArgs(array('testme.php', '--', 'status'));
        $result = $this->runModel($model);

        $this->assertNotContains('testme.php', $result);
        $this->assertNotContains('Usage:', $result);

        /** @var \Magento\Indexer\Model\Indexer\Collection $indexerCollection */
        $indexerCollection = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Indexer\Model\Indexer\Collection'
        );
        foreach ($indexerCollection->getItems() as $indexer) {
            /** @var \Magento\Indexer\Model\IndexerInterface $indexer */
            $this->assertContains($indexer->getTitle(), $result);
        }
    }

    /**
     * @param string $param
     * @param bool $expectedHasErrors
     *
     * @dataProvider hasErrorsDataProvider
     */
    public function testHasErrors($param, $expectedHasErrors)
    {
        $model = $this->getModel('testme.php');
        $model->setRawArgs(array('testme.php', '--', $param));
        $this->runModel($model);

        $this->assertEquals($expectedHasErrors, $model->hasErrors());
    }

    /**
     * @return array
     */
    public function hasErrorsDataProvider()
    {
        return array(
            'execution without issues' => array('info', false),
            'issue with wrong index' => array('--reindex=wrong_index_code', true)
        );
    }
}
