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
        $shell = $this->shellFactory->create(array('entryPoint' => $this->entryFileName));
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
