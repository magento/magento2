<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Controller;

use Magento\Framework\Composer\ComponentReader;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ComponentManager extends AbstractActionController
{
    /**
     * @var ComponentReader
     */
    private $reader;

    /**
     * @param ComponentReader $reader
     */
    public function __construct(ComponentReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @inheritdoc
     */
    public function indexAction()
    {
        $components = $this->reader->getComponents();
        return new JsonModel(['success' => true, 'components' => $components]);
    }
}
