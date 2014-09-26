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
namespace Magento\Setup\Controller\Data;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Magento\Setup\Model\FilePermissions;

class FilePermissionsController extends AbstractActionController
{
    /**
     * @var JsonModel
     */
    protected $jsonModel;

    /**
     * @var FilePermissions
     */
    protected $permissions;

    /**
     * @param JsonModel $jsonModel
     * @param FilePermissions $permissions
     */
    public function __construct(
        JsonModel $jsonModel,
        FilePermissions $permissions
    ) {
        $this->jsonModel = $jsonModel;
        $this->permissions = $permissions;
    }

    /**
     * @return JsonModel
     */
    public function indexAction()
    {
        $responseType = ResponseTypeInterface::RESPONSE_TYPE_SUCCESS;
        if ($this->permissions->checkPermission()) {
            $responseType = ResponseTypeInterface::RESPONSE_TYPE_ERROR;
        }

        $data = [
            'responseType' => $responseType,
            'data' => [
                'required' => $this->permissions->getRequired(),
                'current' => $this->permissions->getCurrent(),
            ],
        ];

        return $this->jsonModel->setVariables($data);
    }
}