<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class FeedbackControllerTest extends WebTestCase
{
    public const URI = '/feedback';

    public const FORM_SELECTOR = 'form[name="feedback_form"]';

    public function testUsersCanLeaveFeedback(): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, self::URI);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists(self::FORM_SELECTOR);
        $this->assertSelectorExists('form button[type="submit"]');
    }

    public function testFeedbackFormIsProtectedByCaptcha(): void
    {
        $client = static::createClient();

        $client->request(Request::METHOD_GET, self::URI);

        $this->assertSelectorExists('input[name="feedback_form[captcha]"]');
    }
}
