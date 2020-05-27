<?php

namespace App\Tests\Controller;

use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// class ConferenceControllerTest extends PantherTestCase
class ConferenceControllerTest extends WebTestCase
{
  public function testIndex()
  {
    // $client = static::createPantherClient(['external_base_uri' => $_SERVER['SYMFONY_DEFAULT_ROUTE_URL']]);
    $client = static::createClient();
    $client->request('GET', '/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h2', 'Give your feedback');
  }

  public function testCommentSubmission()
  {
    $client = static::createClient();
    $client->request('GET', '/conference/amsterdam-2019');
    $client->submitForm('Submit', [
      'comment_form[author]' => 'Mirko',
      'comment_form[text]' => 'Some feedback from an automated functional test',
      'comment_form[email]' => $email = 'me@automat.ed',
      'comment_form[photo]' => dirname(__DIR__, 2).'/public/images/under-construction.gif',
    ]);

    $this->assertResponseRedirects();

    // simulate comment validation
    $comment = self::$container->get(CommentRepository::class)->findOneByEmail($email);
    $comment->setState('published');
    self::$container->get(EntityManagerInterface::class)->flush();

    $client->followRedirect();
    $this->assertSelectorExists('div:contains("There are 2 comments.")');
  }

  public function testConferencePage()
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/');

    $this->assertCount(2, $crawler->filter('h4'));

    $client->clickLink('View');

    $this->assertPageTitleContains('Amsterdam');
    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h2', 'Amsterdam 2019');
    $this->assertSelectorExists('div:contains("There are 1 comments")');
  }

  // public function testMailerAssertions()
  // {
  //   $client = static::createClient();
  //   $client->request('GET', '/');

  //   $this->assertEmailCount(1);

  //   $event = $this->getMailerEvent(0);
  //   $this->assertEmailsQueued($event);

  //   $email = $this->getMailerMessage(0);
  //   $this->assertEmailHeaderSame($email, 'To', 'mirko@example.com');
  //   $this->assertEmailTextBodyContains($email, 'Bar');
  //   $this->assertEmailAttachmentCount($email, 1);
  // }
}