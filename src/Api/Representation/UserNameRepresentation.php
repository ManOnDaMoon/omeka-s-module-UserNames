<?php
namespace UserNames\Api\Representation;

use Omeka\Api\Representation\AbstractEntityRepresentation;

class UserNameRepresentation extends AbstractEntityRepresentation
{
    public function getControllerName()
    {
        return 'username';
    }

    public function getJsonLdType()
    {
        return 'o:UserName';
    }

    public function getJsonLd()
    {
        /** @var \UserNames\Entity\UserNames $entity */
        $entity = $this->resource;
        return [
            'o:user' => $entity->getUserId(),
            'o-module-usernames:username' => $entity->getUserName(),
        ];
    }

    public function id()
    {
        return $this->resource->getId();
    }

    public function user()
    {
        return $this->getAdapter('users')->getRepresentation($this->resource->getUser());
        //return $this->resource->getUserId();
    }

    public function userName()
    {
        return $this->resource->getUserName();
    }

    public function getEntity()
    {
        return $this->resource;
    }
}
