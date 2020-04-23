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
use Zend\Validator\Regex;

class UserNameAdapter extends AbstractEntityAdapter
{
    // TODO: make the following constraints configurable
    const USERNAME_MIN_LENGTH = 1;
    const USERNAME_MAX_LENGTH = 30;

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
            if ($this->shouldHydrate($request, 'id')) {
                $entity->setId($request->getValue('id'));
            }
            if ($this->shouldHydrate($request, 'o-module-usernames:username')) {
                $entity->setUserName($request->getValue('o-module-usernames:username'));
            }
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        if (!empty($query['id'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.id",
                $this->createNamedParameter($qb, $query['id']))
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

        if (!$this->isUnique($entity, ['userName' => $userName])) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'The user name %s is already taken.', // @translate
                $userName
                ));
        }

        if (strlen($userName) < self::USERNAME_MIN_LENGTH || strlen($userName) > self::USERNAME_MAX_LENGTH) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'User name must be between %1$s and %2$s characters.', // @translate
                self::USERNAME_MIN_LENGTH, self::USERNAME_MAX_LENGTH
                ));
        }

        $validator = new Regex('#^[a-zA-Z0-9.*@+!\-_%\#\^&$]*$#u');
        if (!$validator->isValid($userName)) {
            $errorStore->addError('o-module-usernames:username', new Message(
                'Whitespace is not allowed. Only these special characters may be used: %s', // @translate
                ' + ! @ # $ % ^ & * . - _'
                ));
        }


    }
}