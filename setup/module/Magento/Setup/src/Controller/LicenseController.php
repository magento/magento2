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
namespace Magento\Setup\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Magento\Setup\Model\License;

/**
 * Class LicenseController
 *
 * @package Magento\Setup\Controller
 */
class LicenseController extends AbstractActionController
{
    /**
     * View object
     *
     * @var \Zend\View\Model\ViewModel
     */
    protected $view;

    /**
     * Licence Model
     *
     * @var License
     */
    protected $license;

    /**
     * Constructor
     *
     * @param License $license
     * @param ViewModel $view
     */
    public function __construct(License $license, ViewModel $view)
    {
        $this->license = $license;
        $this->view = $view;
    }

    /**
     * Displays license
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $contents = $this->license->getContents();
        if ($contents === false) {
            $this->view->setTemplate('error/404');
            $this->view->setVariable('message', 'Cannot find license file.');
        } else {
            $this->view->setVariable('license', $contents);
        }
        return $this->view;
    }
}
