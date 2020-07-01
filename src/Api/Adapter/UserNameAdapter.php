<?php
namespace UserNames\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use UserNames\Api\Representation\UserNameRepresentation;
use UserNames\Entity\UserNames;
use Omeka\Stdlib\Message;
use Laminas\Validator\Regex;

class UserNameAdapter extends AbstractEntityAdapter
{
    public function getResourceName()
    {
        return 'usernames';
    }

    public function getRepresentationClass()
    {
        return UserNameRepresentation::class;
    }

    public function getEntityClass()
    {
        return UserNames::class;
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
        ) {
        if ($this->shouldHydrate($request, 'user')) {
            $userAdapter = $this->getAdapter('users');
            $user = $userAdapter->findEntity($request->getValue('user'));
            $entity->setUser($user);
        }

        if ($this->shouldHydrate($request, 'o-module-usernames:username')) {
            $entity->setUserName($request->getValue('o-module-usernames:username'));
        }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (!empty($query['user'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.user",
                $this->createNamedParameter($qb, $query['user']))
                );
        }
        if (!empty($query['userName'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.userName",
                $this->createNamedParameter($qb, $query['userName']))
                );
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        $userName = $entity->getUserName();

        if (!$userName) {
            $errorStore->addError('o-module-usernames:username', 'The user name cannot be empty.'); // @translate
        }

        $globalSettings = $this->getServiceLocator()->get('Omeka\Settings');
        $userNamesMinLength = $globalSettings->get('usernames_min_length');
        $userNamesMaxLength = $globalSettings->get('usernames_max_length');
        if (strlen($userName) < $userNamesMinLength || strlen($userName) > $userNamesMaxLength) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'User name must be between %1$s and %2$s characters.', // @translate
                $userNamesMinLength, $userNamesMaxLength
                ));
        }

        $validator = new Regex('#^[a-zA-Z0-9.*@+!\-_%\#\^&$]*$#u');
        if (!$validator->isValid($userName)) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'Whitespace is not allowed. Only these special characters may be used: %s', // @translate
                ' + ! @ # $ % ^ & * . - _'
                ));
        }

        if (!$this->isUnique($entity, ['userName' => $userName])) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'The user name %s is already taken.', // @translate
                $userName
                ));
        }
    }
}
