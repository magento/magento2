<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\Test\Unit\TemplateEngine\Xhtml;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout\Generator\Structure;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\CompilerInterface;
use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use Magento\Ui\TemplateEngine\Xhtml\Result;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Psr\Log\LoggerInterface;

/**
 * Test Class for Class Result.
 * @see \Magento\Ui\TemplateEngine\Xhtml\Result
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Template|MockObject
     */
    private $template;

    /**
     * @var CompilerInterface|MockObject
     */
    private $compiler;

    /**
     * @var UiComponentInterface|MockObject
     */
    private $component;

    /**
     * @var Structure|MockObject
     */
    private $structure;

    /**
     * @var LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Result
     */
    private $testModel;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->template = $this->createPartialMock(Template::class, ['append']);
        $this->compiler = $this->createMock(CompilerInterface::class);
        $this->component = $this->createMock(UiComponentInterface::class);
        $this->structure = $this->createPartialMock(Structure::class, ['generate']);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->objectManager = new ObjectManager($this);
        $this->testModel = $this->objectManager->getObject(Result::class, [
            'template' => $this->template,
            'compiler' => $this->compiler,
            'component' => $this->component,
            'structure' => $this->structure,
            'logger' => $this->logger,
        ]);
    }

    /**
     * Test Append layout configuration method
     */
    public function testAppendLayoutConfiguration()
    {
        $configWithCdata = 'text before <![CDATA[cdata text]]>';
        $this->structure->expects($this->once())
            ->method('generate')
            ->with($this->component)
            ->willReturn([$configWithCdata]);
        $this->template->expects($this->once())
            ->method('append')
            ->with('<script type="text/x-magento-init"><![CDATA[{"*": {"Magento_Ui/js/core/app": '
                . '["text before \u003C![CDATA[cdata text]]\u003E"]'
                . '}}]]></script>');

        $this->testModel->appendLayoutConfiguration();
    }
}
