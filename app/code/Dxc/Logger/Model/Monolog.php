<?php

namespace Dxc\Logger\Model;


class Monolog extends \Magento\Framework\Logger\Monolog
{
    /**
     * {@inheritdoc}
     */
    public function __construct($name, array $handlers = [], array $processors = [], \Dxc\Logger\Helper\Data $helper)
    {
        $podDetails = $helper->getKubernetesPodDetails();
        $name = is_null($podDetails) ? $name : $podDetails;
        $handlers = array_values($handlers);
        parent::__construct($name, $handlers, $processors);
    }
}
