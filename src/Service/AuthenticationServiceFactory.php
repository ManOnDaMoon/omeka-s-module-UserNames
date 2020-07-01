<?php
namespace UserNames\Service;

use Omeka\Authentication\Adapter\KeyAdapter;
use UserNames\Authentication\Adapter\PasswordAdapter;
use Omeka\Authentication\Storage\DoctrineWrapper;
use Laminas\Authentication\AuthenticationService;
use Laminas\Authentication\Adapter\Callback;
use Laminas\Authentication\Storage\NonPersistent;
use Laminas\Authentication\Storage\Session;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * UserNames : Authentication service factory replaces core Omeka S class
 * to invoke the proper PasswordAdapter when needed.
 */
class AuthenticationServiceFactory implements FactoryInterface
{
    /**
     * Create the authentication service.
     *
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $serviceLocator, $requestedName, array $options = null)
    {
        $entityManager = $serviceLocator->get('Omeka\EntityManager');
        $status = $serviceLocator->get('Omeka\Status');

        // Skip auth retrieval entirely if we're installing or migrating.
        if (!$status->isInstalled() ||
            ($status->needsVersionUpdate() && $status->needsMigration())
        ) {
            $storage = new NonPersistent;
            $adapter = new Callback(function () {
                return null;
            });
        } else {
            $userRepository = $entityManager->getRepository('Omeka\Entity\User');
            if ($status->isApiRequest()) {
                // Authenticate using key for API requests.
                $keyRepository = $entityManager->getRepository('Omeka\Entity\ApiKey');
                $storage = new DoctrineWrapper(new NonPersistent, $userRepository);
                $adapter = new KeyAdapter($keyRepository, $entityManager);
            } else {
                // Authenticate using user/password for all other requests.
                $userNameRepository = $entityManager->getRepository('UserNames\Entity\UserNames');
                $storage = new DoctrineWrapper(new Session, $userRepository);
                $adapter = new PasswordAdapter($userRepository, $userNameRepository);
            }
        }

        $authService = new AuthenticationService($storage, $adapter);
        return $authService;
    }
}
