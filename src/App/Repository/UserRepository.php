<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Constraints\Email;

class UserRepository implements UserProviderInterface{

    protected $conn;
    protected $encoder;

    public function __construct(Connection $conn, $encoder)
    {
        $this->conn = $conn;
        $this->encoder = $encoder;
    }

    public function find($id){
        $userData = $this->conn->fetchAssoc('SELECT * FROM users WHERE id = ?', array($id));
        return $this->buildUser($userData);
    }

    public function findByEmail($email){
        $userData = $this->conn->fetchAssoc('SELECT * FROM users WHERE email = ?', array($email));
        return $this->buildUser($userData);
    }
    public function findAll(){
        $query = 'SELECT * FROM users';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        if(!$userData = $stmt->fetchAll()){
            throw new Exception('Users not found');
        }
        $users = [];
        foreach($userData as $user){
            $users[] = $this->buildUser($user);
        }

        return $users;
    }

    public function save(User $user){
        $this->conn->insert('users', array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password' => $this->encoder->encodePassword($user->getPassword(), null),
            'role' => $user->getRole(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ));
        $id = $this->conn->lastInsertId();
        $user->setId($id);
    }

    public function delete($id){
        return $this->conn->delete('users', array('id' => $id));
    }

    public function update(User $user){
        $this->conn->update('users',array(
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role' => $user->getRole(),
        ), array('id' => $user->getId()));
    }

    public function buildUser($user){
        if (!$user){
            return false;
        }
        return new User(
            $user['id'],
            $user['username'],
            $user['email'],
            $user['password'],
            $user['role'],
            DateTime::createFromFormat('Y-m-d H:i:s', $user['created_at'])
        );
    }
    /**
     * Loads the user for the given username.
     *
     * This method must throw UsernameNotFoundException if the user is not
     * found.
     *
     * @param string $username The username
     *
     * @return UserInterface
     *
     * @throws UsernameNotFoundException if the user is not found
     */
    public function loadUserByUsername($username)
    {
        $query = 'SELECT * FROM users WHERE email = ?';
        if (!$userdata = $this->conn->fetchAssoc($query, array($username))) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }
        $user = new User(
            $userdata['id'],
            $userdata['username'],
            $userdata['email'],
            $userdata['password'],
            $userdata['role'],
            $userdata['created_at']
        );

        return $user;
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the UserInterface
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     *
     * @return UserInterface
     *
     * @throws UnsupportedUserException if the account is not supported
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Whether this provider supports the given user class.
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        // TODO: Implement supportsClass() method.
    }
}