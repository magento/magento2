<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\View\TemplateEngine;

use Magento\Framework\View\Element\BlockInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing .phtml templating.
 */
class PhpTest extends TestCase
{
    /**
     * @var Php
     */
    private $templateEngine;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->templateEngine = $objectManager->get(Php::class);
    }

    /**
     * See that templates get access to certain variables.
     *
     * @return void
     */
    public function testVariablesAvailable(): void
    {
        $block = new class implements BlockInterface {
            /**
             * @inheritDoc
             */
            public function toHtml()
            {
                return '<b>BLOCK</b>';
            }
        };

        $rendered = $this->templateEngine->render($block, __DIR__ .'/../_files/test_template.phtml');
        $this->assertEquals(
            '<p>This template has access to &lt;b&gt;$escaper&lt;/b&gt; and $block &quot;<b>BLOCK</b>&quot;</p>'
            .PHP_EOL,
            $rendered
        );
    }
}
