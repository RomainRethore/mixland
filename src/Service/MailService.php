<?php

namespace App\Service;

use App\Entity\Mix;
use App\Entity\User;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class MailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendMixCreatedEmail(Mix $mix, User $user)
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to('user@example.com')
            ->subject('New Mix Created')
            ->html('<p>A new mix has been created: ' . $mix->getTitle() . ' by ' . $user->getName() . '</p>');

        $this->mailer->send($email);
    }

    public function sendUserLoggedInMail(User $user)
    {
        $email = (new Email())
            ->from('no-reply@example.com')
            ->to('user@example.com')
            ->subject('New User Logged In')
            ->html('<p>A new user has logged in: ' . $user->getName() . '</p>');

        $this->mailer->send($email);
    }
}
