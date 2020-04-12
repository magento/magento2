<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit\TemplateEngine\Xhtml;

use Magento\Framework\View\TemplateEngine\Xhtml\Template;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test XML template engine
 */
class TemplateTest extends TestCase
{
    /**
     * @var Template
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = new Template(
            $this->getMockForAbstractClass(LoggerInterface::class),
            file_get_contents(__DIR__ . '/../_files/simple.xml')
        );
    }

    /**
     * Test that xml content is correctly appended to the current element
     */
    public function testAppend()
    {
        $body = <<<HTML
<body>
    <h1>Home Page</h1>
    <p>CMS homepage content goes here.</p>
</body>
HTML;
        $expected = <<<HTML
<!--
/**
 * Copyright &copy; Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
--><html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Home Page</title>
    </head>
<body>
    <h1>Home Page</h1>
    <p>CMS homepage content goes here.</p>
</body></html>

HTML;

        $this->model->append($body);
        $this->assertEquals($expected, (string) $this->model);
    }
}
