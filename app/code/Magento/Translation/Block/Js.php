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

namespace Magento\Translation\Block;

use Magento\Framework\View\Element\Template;
use \Magento\Translation\Model\Js as DataProvider;
use \Magento\Framework\Translate\InlineInterface as InlineTranslator;

class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * Data provider model
     *
     * @var DataProvider
     */
    protected $dataProvider;

    /**
     * Inline translator
     *
     * @var InlineTranslator
     */
    protected $translateInline;

    /**
     * @param Template\Context $context
     * @param DataProvider $dataProvider
     * @param InlineTranslator $translateInline
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DataProvider $dataProvider,
        InlineTranslator $translateInline,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->dataProvider = $dataProvider;
        $this->translateInline = $translateInline;
    }

    /**
     * @return string
     */
    public function getTranslatedJson()
    {
        $json = \Zend_Json::encode($this->dataProvider->getTranslateData());
        $this->translateInline->processResponseBody($json, false);
        return $json;
    }
}
