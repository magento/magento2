<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Validator;

use Magento\Cms\Model\Validator\DirectiveValidator;
use PHPUnit\Framework\TestCase;

class DirectiveValidatorTest extends TestCase
{
    /**
     * @var DirectiveValidator
     */
    private DirectiveValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new DirectiveValidator();
    }

    public function testAllowsCleanBlockDirective(): void
    {
        $html = '{{block class="Magento\\Customer\\Block\\Form\\Register"
        name="home.form.customattributes" template="Magento_Customer::form/register.phtml"}}';
        $this->assertTrue($this->validator->isValid($html));
    }

    public function testRejectsBlockInsidePre(): void
    {
        $html = '<pre class="code-java">{{block class="A\\B" id="x"}}</pre>';
        $this->assertFalse($this->validator->isValid($html));
    }

    public function testRejectsHtmlInjectedParams(): void
    {
        $html = '{{block class=<span class="code-quote">"A\\B"</span> id="x"}}';
        $this->assertFalse($this->validator->isValid($html));
    }

    public function testRequiresClassOrId(): void
    {
        $html = '{{block name="only-name"}}';
        $this->assertFalse($this->validator->isValid($html));
    }

    public function testInvalidClassCharacters(): void
    {
        $html = '{{block class="Bad-Class" id="x"}}';
        $this->assertFalse($this->validator->isValid($html));
    }

    public function testValidClassCharacters(): void
    {
        $html = '{{block class="Vendor\\Module\\Block\\My_Block" id="x"}}';
        $this->assertTrue($this->validator->isValid($html));
    }

    public function testValidTemplateCharacters(): void
    {
        $html = '{{block class="A\\B" template="Magento_Customer::form/register.phtml"}}';
        $this->assertTrue($this->validator->isValid($html));
    }

    public function testInvalidIdCharacters(): void
    {
        $html = '{{block id="bad id"}}';
        $this->assertFalse($this->validator->isValid($html));
    }

    public function testValidIdCharacters(): void
    {
        $html = '{{block id="good_id-123"}}';
        $this->assertTrue($this->validator->isValid($html));
    }
}
