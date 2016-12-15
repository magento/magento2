<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\File\Resource;

use Magento\Mtf\ObjectManagerInterface;
use Magento\Mtf\Util\Protocol\CurlTransport;
use Magento\Mtf\Util\Protocol\CurlInterface;

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
     * @param ObjectManagerInterface $objectManager
     * @param CurlTransport $transport
     * @param string $template
     */
    public function __construct(ObjectManagerInterface $objectManager, CurlTransport $transport, $template)
    {
        $this->objectManager = $objectManager;
        $this->template = $template;
        $this->transport = $transport;
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
        $this->transport->write($this->prepareUrl(), [], CurlInterface::GET);
        $serializedFiles = $this->transport->read();
        $this->transport->close();

        return unserialize($serializedFiles);
    }

    /**
     * Prepare url.
     *
     * @return string
     */
    private function prepareUrl()
    {
        return $_ENV['app_frontend_url'] . self::URL . '?template=' . urlencode($this->template);
    }
}
