<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks if .htaccess is identical to .htaccess.sample.
 */
class HtaccessAnalyzer extends \Symfony\Component\Console\Command\Command
{
    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Command path.
     *
     * @var string
     */
    private $commandPath = 'dev/tests/functional/utils/command.php?command=';

    /**
     * HTTP CURL Adapter.
     *
     * @var CurlTransport
     */
    private $curl;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param CurlTransport $curl
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        CurlTransport $curl
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->curl = $curl;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:check-htaccess')
            ->setDescription('Check .htaccess file is present. It is needed to run cli commands via browser url.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        \PHPUnit\Util\Configuration::getInstance(MTF_PHPUNIT_FILE)->handlePHPConfiguration();
        $output = $this->objectManager->create(
            \Magento\Mtf\Console\Output::class,
            ['output' => $output]
        );
        try {
            $output->writeln("Checking .htaccess file...");
            $this->curl->write($_ENV['app_frontend_url'] . $this->commandPath, [], CurlInterface::GET);
            $this->curl->read();
            $responseCode = $this->curl->getInfo(CURLINFO_HTTP_CODE);
            if ($responseCode != 200) {
                $message['error'][] = 'Your .htaccess file doesn\'t exist. '
                    . 'Please, create it from to .htaccess.sample.';
                $output->outputMessages($message);
            }
            $this->curl->close();
        } catch (\Exception $e) {
            $output->outputMessages(['error' => [$e->getMessage()]]);
        }
        $output->writeln(".htaccess check finished.");
    }
}
