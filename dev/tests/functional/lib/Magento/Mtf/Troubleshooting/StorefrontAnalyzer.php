<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Troubleshooting;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Troubleshooting\Helper\UrlAnalyzer;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Analyze URL specified in the phpunit.xml.
 */
class StorefrontAnalyzer extends \Symfony\Component\Console\Command\Command
{
    /**
     * HTTP CURL Adapter.
     *
     * @var CurlTransport
     */
    private $curlTransport;

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Url analyzer helper.
     *
     * @var UrlAnalyzer
     */
    private $urlAnalyzer;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param UrlAnalyzer $urlAnalyzer
     * @param CurlTransport $curlTransport
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        UrlAnalyzer $urlAnalyzer,
        CurlTransport $curlTransport
    ) {
        parent::__construct();
        $this->objectManager = $objectManager;
        $this->urlAnalyzer = $urlAnalyzer;
        $this->curlTransport = $curlTransport;
    }

    /**
     * Configure command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
        $this->setName('troubleshooting:check-magento-storefront')
            ->setDescription('Check that app_frontend_url config is correct and Magento installed.');
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
        \PHPUnit_Util_Configuration::getInstance(MTF_PHPUNIT_FILE)->handlePHPConfiguration();
        $output = $this->objectManager->create(
            \Magento\Mtf\Console\Output::class,
            ['output' => $output]
        );
        $output->writeln("Verifying Magento Storefront...");
        $storefrontUrlAnalyzerMessages = $this->runStorefrontUrlAnalyzer();
        if (isset($storefrontUrlAnalyzerMessages['error']) === false) {
            $output->outputMessages($this->urlAnalyzer->fixLastSlash('app_frontend_url'));
            $output->outputMessages($this->urlAnalyzer->checkDomain($_ENV['app_frontend_url']));
        } else {
            $output->outputMessages($storefrontUrlAnalyzerMessages);
        }
        $output->writeln("Storefront verification finished.");
    }

    /**
     * Run Storefront url analyzer check.
     *
     * @return array
     */
    private function runStorefrontUrlAnalyzer()
    {
        $messages = [];
        if (!isset($_ENV['app_frontend_url'])) {
            $messages['error'][] = 'app_frontend_url parameter is absent in the phpunit.xml file. '
                . 'Please, copy file from phpunit.xml.dist.';
            return $messages;
        }
        $url = $_ENV['app_frontend_url'];
        try {
            $this->curlTransport->write($url, [], CurlInterface::GET);
            $response = $this->curlTransport->read();
            if (strpos($response, 'Home Page') === false) {
                $messages['error'][] = 'Magento seems not installed. Check your Magento instance.';
            }
        } catch (\Exception $e) {
            $messages['error'][] = $e->getMessage();
        }
        $this->curlTransport->close();

        return $messages;
    }
}
