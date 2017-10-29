<?php

namespace Drupal\anonymous_redirect_option\EventSubscriber;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class AnonymousRedirectOptSubscriber.
 */
class AnonymousRedirectOptSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    $events[KernelEvents::REQUEST][] = ['anonymousRedirect', 100];

    return $events;
  }

  /**
   * This method is called whenever the kernel.request event is dispatched.
   *
   * @param GetResponseEvent $event
   *   Dispatched Event Object.
   */
  public function anonymousRedirect(Event $event) {
    // Getting the current request path.
    $currentPath = $event->getRequest()->getPathInfo();

    // Getting the current user.
    $currentUser = \Drupal::currentUser();

    if ($currentUser->isAuthenticated()) {
      return;
    }

    // @todo need to create config.
    $pathRedirectOptions = [
      '/user/password',
    ];

    if ($currentUser->isAnonymous() && in_array($currentPath, $pathRedirectOptions)) {
      $event->setResponse(new RedirectResponse(Url::fromRoute('user.login')->toString()));
    }

  }

}
