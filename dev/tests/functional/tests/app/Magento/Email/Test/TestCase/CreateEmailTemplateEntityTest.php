<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Email\Test\TestCase;

use Magento\Email\Test\Fixture\EmailTemplate;
use Magento\Email\Test\Page\Adminhtml\EmailTemplateIndex;
use Magento\Email\Test\Page\Adminhtml\EmailTemplateNew;
use Magento\Mtf\TestCase\Injectable;

/**
 * Steps:
 * 1. Log in to Admin.
 * 2. Open the Email Templates page.
 * 3. Click the "Add New Template" button.
 * 4. Select Email Template.
 * 5. Click the "Load Template" button.
 * 6. Enter Email Template name.
 * 7. Click the "Save" button.
 * 8. Verify the email template saved successfully.
 * @group Email_(PS)
 * @ZephyrId MAGETWO-17155
 */

class CreateEmailTemplateEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'PS';
    const TO_MAINTAIN = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Email Template Index page.
     *
     * @var EmailTemplateIndex
     */
    private $emailTemplateIndex;

    /**
     * New EmailTemplate page.
     *
     * @var EmailTemplateNew
     */
    private $emailTemplateNew;

    /**
     * Inject Email template pages.
     *
     * @param EmailTemplateIndex $emailTemplateIndex
     * @param EmailTemplateNew $emailTemplateNew
     * @return void
     */
    public function __inject(
        EmailTemplateIndex $emailTemplateIndex,
        EmailTemplateNew $emailTemplateNew
    ) {
        $this->emailTemplateIndex = $emailTemplateIndex;
        $this->emailTemplateNew = $emailTemplateNew;
    }

    /**
     * @param EmailTemplate $emailTemplate
     */
    public function test(EmailTemplate $emailTemplate)
    {
        $this->emailTemplateIndex->open();
        $this->emailTemplateIndex->getPageActionsBlock()->addNew();
        $this->emailTemplateNew->getTemplateForm()->fill($emailTemplate);
        $this->emailTemplateNew->getTemplateForm()->clickLoadTemplate();
        $this->emailTemplateNew->getFormPageActions()->save();
    }
}
