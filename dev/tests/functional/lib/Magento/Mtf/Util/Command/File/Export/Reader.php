<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Mtf\Util\Command\File\Export;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlInterface;
use Magento\Mtf\Util\Protocol\CurlTransport\WebapiDecorator;

/**
 * File reader for Magento export files.
 */
class Reader implements ReaderInterface
{
    /**
     * Pattern for file name in Magento.
     *
     * @var string
     */
    private $template;

    /**
     * Object manager instance.
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Curl transport protocol.
     *
     * @var CurlTransport
     */
    private $transport;

    /**
     * Webapi handler.
     *
     * @var WebapiDecorator
     */
    private $webapiHandler;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param CurlTransport $transport
     * @param WebapiDecorator $webapiHandler
     * @param string $template
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        CurlTransport $transport,
        WebapiDecorator $webapiHandler,
        $template
    ) {
        $this->objectManager = $objectManager;
        $this->template = $template;
        $this->transport = $transport;
        $this->webapiHandler = $webapiHandler;
    }

    /**
     * Exporting files as Data object from Magento.
     *
     * @return Data[]
     */
    public function getData()
    {
        $data = [];
        foreach ($this->getFiles() as $file) {
            $data[] = $this->objectManager->create(Data::class, ['data' => $file]);
        }

        return $data;
    }

    /**
     * Get files by template from the Magento.
     *
     * @return array
     */
    private function getFiles()
    {
        $this->transport->write(
            rtrim(str_replace('index.php', '', $_ENV['app_frontend_url']), '/') . self::URL,
            $this->prepareParamArray(),
            CurlInterface::POST,
            []
        );
        $serializedFiles = $this->transport->read();
        $this->transport->close();
        // phpcs:ignore Magento2.Security.InsecureFunction
        return unserialize($serializedFiles);
    }

    /**
     * Prepare parameter array.
     *
     * @return array
     */
    private function prepareParamArray()
    {
        return [
            'token' => urlencode($this->webapiHandler->getWebapiToken()),
            'template' => urlencode($this->template)
        ];
    }
}
