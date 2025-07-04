<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class InstallationLockedSubscriber implements EventSubscriberInterface
{
    public function onResponseEvent(ResponseEvent $event): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists(__DIR__ . '/../../var/install.lock')) {
            if ($event->getRequest()->getrequestUri() !== '/install') {
                $event->setResponse(
                    new Response(sprintf(
                'Installation has not been run. Please navigate to the <a href="%s">installer</a>.',
                $event->getRequest()->getSchemeAndHttpHost() . '/install')
                    )
                );
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ResponseEvent::class => 'onResponseEvent',
        ];
    }
}
