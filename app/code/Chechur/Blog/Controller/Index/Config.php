<?php
declare(strict_types=1);

namespace Chechur\Blog\Controller\Index;

use Chechur\Blog\Helper\Data;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Config extends Action
{

    protected $helperData;

    public function __construct(
        Context $context,
        Data $helperData

    )
    {
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    public function execute()
    {

        // TODO: Implement execute() method.

        echo $this->helperData->getGeneralConfig('enable') . "<br>";
        echo $this->helperData->getGeneralConfig('display_text') . "<br>";
        echo $this->helperData->getGeneralConfig('multiselect') . "<br>";
        exit();

    }
}
