<?php
namespace UserNames\Authentication\Adapter;

use Doctrine\ORM\EntityRepository;
use Zend\Authentication\Adapter\AbstractAdapter;
use Zend\Authentication\Result;

/**
 * Auth adapter for checking passwords through Doctrine.
 */
class PasswordAdapter extends AbstractAdapter
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * Create the adapter.
     *
     * @param EntityRepository $repository The User repository.
     */
    public function __construct(EntityRepository $repository)
    {
        $this->setRepository($repository);
    }

    public function authenticate()
    {
        $user = $this->repository->findOneBy(['email' => $this->identity]);

        if (!$user) { //Seems to work?
            $qb = $this->repository->createQueryBuilder('*');
            $qb->join('user_names', 'user_id')->where('user_name = ' . $this->identity);
            $query = $qb->getQuery();
            $user = $query->getFirstResult();
        }

        if (!$user || !$user->isActive()) {
            return new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null,
                ['User not found.']);
        }

        if (!$user->verifyPassword($this->credential)) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null,
                ['Invalid password.']);
        }

        return new Result(Result::SUCCESS, $user);
    }

    /**
     * Set the repository to use to look up users.
     *
     * @param EntityRepository $repository
     */
    public function setRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get the repository used to look up users.
     *
     * @return EntityRepository
     */
    public function getRepository()
    {
        return $this->repository;
    }
}
