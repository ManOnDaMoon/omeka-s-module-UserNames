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
        $entity = $this->resource;
        return [
            'id' => $entity->getId(),
            'o-module-usernames:username' => $entity->getUserName(),
        ];
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