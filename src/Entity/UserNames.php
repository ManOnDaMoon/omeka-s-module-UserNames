<?php
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
    protected $userId;

    /**
     * @Column(type="string", length=190, unique=true)
     */
    protected $userName;

    /**
     * Get the unique ID for this resource.
     */
    public function getId()
    {
        return $this->userId;
    }
}