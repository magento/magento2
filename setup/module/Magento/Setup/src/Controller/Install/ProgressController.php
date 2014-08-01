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

use Magento\Module\ModuleListInterface;
use Magento\Setup\Model\Logger;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ProgressController extends AbstractActionController
{
    /**
     * @var \Zend\View\Model\JsonModel
     */
    protected $json;

    /**
     * @var Logger
     */
    protected $logger;

    protected $moduleList;

    /**
     * @param JsonModel $view
     * @param ModuleListInterface $moduleList
     * @param Logger $logger
     */
    public function __construct(
        JsonModel $view,
        ModuleListInterface $moduleList,
        Logger $logger
    ) {
        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->json = $view;
    }

    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        //@todo I fix it
        $moduleCount = count($this->moduleList->getModules());
        $log = $this->logger->get();
        $progress = 0;
        if (!empty($log)) {
            $progress = round(count($log)/$moduleCount*90);
        }
        $progress += 5;

        return $this->json->setVariables(
            array(
                'progress' => $progress,
                'success' => !$this->logger->hasError(),
                'console' => $log
            )
        );
    }
}
