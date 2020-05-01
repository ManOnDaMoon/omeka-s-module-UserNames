<?php
namespace UserNames\Entity;

use Omeka\Entity\AbstractEntity;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class UserNames extends AbstractEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @OneToOne(
     *     targetEntity="Omeka\Entity\User"
     * )
     * @JoinColumn(
     *     nullable=true,
     *     onDelete="CASCADE"
     * )
     */
    protected $user;

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

    public function getUserId()
    {
        return $this->user;
    }

    public function setUserId($userId)
    {
        $this->user = $userId;
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
