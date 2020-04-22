<?php
namespace UserNames\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use Omeka\Api\Adapter\AbstractEntityAdapter;
use UserNames\Api\Representation\UserNameRepresentation;
use UserNames\Entity\UserNames;

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
        if (!empty($query['username'])) {
            $qb->andWhere($qb->expr()->eq(
                "omeka_root.username",
                $this->createNamedParameter($qb, $query['username']))
                );
        }
    }

    public function validateEntity(EntityInterface $entity, ErrorStore $errorStore)
    {
        if (false == $entity->getUserName()) {
            $errorStore->addError('o-module-usernames:username', 'The user name cannot be empty.'); // @translate
        }
    }
}