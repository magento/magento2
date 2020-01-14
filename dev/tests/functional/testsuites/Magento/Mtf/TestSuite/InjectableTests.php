<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\TestSuite;

use Magento\Mtf\ObjectManager;
use Magento\Mtf\ObjectManagerFactory;

/**
 * Class InjectableTests
 *
 */
class InjectableTests extends \PHPUnit\Framework\TestSuite
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit\Framework\TestSuite
     */
    protected $suite;

    /**
     * @var \PHPUnit\Framework\TestResult
     */
    protected $result;

    /**
     * Run collected tests
     *
     * @param \PHPUnit\Framework\TestResult $result
     * @return \PHPUnit\Framework\TestResult|void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function run(\PHPUnit\Framework\TestResult $result = null)
    {
        if ($result === null) {
            $this->result = $this->createResult();
        }
    }

    /**
     * Prepare test suite
     *
     * @return mixed
     */
    public static function suite()
    {
        $suite = new self();
        return $suite->prepareSuite();
    }

    /**
     * Prepare test suite and apply application state
     *
     * @return \Magento\Mtf\TestSuite\AppState
     */
    public function prepareSuite()
    {
        $this->init();
        return $this->objectManager->create(\Magento\Mtf\TestSuite\AppState::class);
    }

    /**
     * Call the initialization of ObjectManager
     */
    public function init()
    {
        $this->initObjectManager();
    }

    /**
     * Initialize ObjectManager
     */
    private function initObjectManager()
    {
        if (!isset($this->objectManager)) {
            $objectManagerFactory = new ObjectManagerFactory();

            $configFileName = isset($_ENV['testsuite_rule']) ? $_ENV['testsuite_rule'] : 'basic';
            $configFilePath = realpath(MTF_BP . '/testsuites/' . $_ENV['testsuite_rule_path']);

            /** @var \Magento\Mtf\Config\DataInterface $configData */
            $configData = $objectManagerFactory->getObjectManager()->create(\Magento\Mtf\Config\TestRunner::class);
            $filter = getopt('', ['filter:']);
            if (!isset($filter['filter'])) {
                $configData->setFileName($configFileName . '.xml')->load($configFilePath);
            } else {
                $isValid = preg_match('`variation::(.*?)$`', $filter['filter'], $variation);
                if ($isValid === 1) {
                    $configData->setFileName($configFileName . '.xml')->load($configFilePath);
                    $data['rule']['variation']['allow'][0]['name'][0]['value'] = $variation[1];
                    $configData->merge($data);
                }
            }
            $this->objectManager = $objectManagerFactory->create(
                [\Magento\Mtf\Config\TestRunner::class => $configData]
            );
        }
    }
}
