<?php
/**
 * Log shell application
 *
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
namespace Magento\Log\App;

use Magento\Framework\App\Console\Response;
use Magento\Framework\App\Bootstrap;
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
        $shell = $this->_shellFactory->create(array('entryPoint' => $this->_entryFileName));
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
