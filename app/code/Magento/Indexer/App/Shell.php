<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\App;

use Magento\Framework\App\Bootstrap;

class Shell implements \Magento\Framework\AppInterface
{
    /**
     * Filename of the entry point script
     *
     * @var string
     */
    protected $entryFileName;

    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $response;

    /**
     * @var \Magento\Indexer\Model\ShellFactory
     */
    protected $shellFactory;

    /**
     * @param string $entryFileName
     * @param \Magento\Indexer\Model\ShellFactory $shellFactory
     * @param \Magento\Framework\App\Console\Response $response
     */
    public function __construct(
        $entryFileName,
        \Magento\Indexer\Model\ShellFactory $shellFactory,
        \Magento\Framework\App\Console\Response $response
    ) {
        $this->entryFileName = $entryFileName;
        $this->shellFactory = $shellFactory;
        $this->response = $response;
    }

    /**
     * Run application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        /** @var $shell \Magento\Indexer\Model\Shell */
        $shell = $this->shellFactory->create(['entryPoint' => $this->entryFileName]);
        $shell->run();
        if ($shell->hasErrors()) {
            $this->response->setCode(-1);
        } else {
            $this->response->setCode(0);
        }
        return $this->response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
