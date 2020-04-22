<?php
namespace UserNames\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 */
class UserNames extends AbstractEntity
{

    /**
     * @Id
     * @Column(type="integer")
     * @OneToOne(targetEntity="User")
     */
    protected $id;

    /**
     * @Column(type="string", length=190, unique=true)
     */
    protected $userName;

    /**
     * Get the unique ID for this resource.
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUserName()
    {
        return $this->userName;
    }

    public function setUserName($userName)
    {
        $this->userName = $userName;
    }
}