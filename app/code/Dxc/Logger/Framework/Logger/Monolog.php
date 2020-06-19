<?php

namespace Dxc\Logger\Framework\Logger;

use Magento\Framework\Logger\Monolog as ParentMonolog;

class Monolog extends ParentMonolog
{

    /**
     * {@inheritdoc}
     */
    public function __construct(\Dxc\Logger\Helper\Data $name, array $handlers = [], array $processors = [])
    {
        $instanceName = $name->getKubernetesPodDetails();
        $handlers = array_values($handlers);
        parent::__construct($instanceName, $handlers, $processors);
    }


}
