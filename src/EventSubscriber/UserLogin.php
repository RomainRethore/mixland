<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Service\MailService;

class UserLogin implements EventSubscriberInterface
{
    private $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public static function getSubscribedEvents()
    {
        return [
            InteractiveLoginEvent::class => 'onUserLoggedIn',
        ];
    }

    public function onUserLoggedIn(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $this->mailService->sendUserLoggedInMail($user);
    }
}
