<?php
/**
 * Log shell application
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Log\App;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Console\Response;
use Magento\Framework\AppInterface;

class Shell implements AppInterface
{
    /**
     * Filename of the entry point script
     *
     * @var string
     */
    protected $_entryFileName;

    /**
     * @var \Magento\Log\Model\ShellFactory
     */
    protected $_shellFactory;

    /**
     * @var \Magento\Framework\App\Console\Response
     */
    protected $_response;

    /**
     * @param string $entryFileName
     * @param \Magento\Log\Model\ShellFactory $shellFactory
     * @param Response $response
     */
    public function __construct($entryFileName, \Magento\Log\Model\ShellFactory $shellFactory, Response $response)
    {
        $this->_entryFileName = $entryFileName;
        $this->_shellFactory = $shellFactory;
        $this->_response = $response;
    }

    /**
     * Run application
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function launch()
    {
        /** @var $shell \Magento\Log\Model\Shell */
        $shell = $this->_shellFactory->create(['entryPoint' => $this->_entryFileName]);
        $shell->run();
        $this->_response->setCode(0);
        return $this->_response;
    }

    /**
     * {@inheritdoc}
     */
    public function catchException(Bootstrap $bootstrap, \Exception $exception)
    {
        return false;
    }
}
