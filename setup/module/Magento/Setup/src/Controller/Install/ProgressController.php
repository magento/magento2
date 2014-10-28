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
namespace Magento\Setup\Controller\Install;

use Magento\Setup\Model\WebLogger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Magento\Setup\Model\Installer\ProgressFactory;

class ProgressController extends AbstractActionController
{
    /**
     * JSON response
     *
     * @var \Zend\View\Model\JsonModel
     */
    protected $json;

    /**
     * Web logger
     *
     * @var WebLogger
     */
    protected $logger;

    /**
     * Progress indicator factory
     *
     * @var ProgressFactory
     */
    protected $progressFactory;

    /**
     * Constructor
     *
     * @param JsonModel $view
     * @param WebLogger $logger
     * @param ProgressFactory $progressFactory
     */
    public function __construct(
        JsonModel $view,
        WebLogger $logger,
        ProgressFactory $progressFactory
    ) {
        $this->logger = $logger;
        $this->json = $view;
        $this->progressFactory = $progressFactory;
    }

    /**
     * Checks progress of installation
     *
     * @return JsonModel
     */
    public function indexAction()
    {
        $percent = 0;
        $success = false;
        try {
            $progress = $this->progressFactory->createFromLog($this->logger);
            $percent = sprintf('%d', $progress->getRatio() * 100);
            $success = true;
            $contents = $this->logger->get();
        } catch (\Exception $e) {
            $contents = [(string)$e];
        }
        return $this->json->setVariables(['progress' => $percent, 'success' => $success, 'console' => $contents]);
    }
}
