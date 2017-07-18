<?php

/*
 * This file is part of the Aureja package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aureja\Bundle\TestFrameworkBundle;

use Aureja\Bundle\TestFrameworkBundle\Exception\LogicException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 9/25/16 7:10 PM
 */
trait DbIsolationTrait
{
    /**
     * @var Connection[]
     */
    private static $dbIsolationConnections = [];

    /**
     * @return Client
     */
    abstract protected function getClient();

    /**
     * Start transaction.
     *
     * @throws LogicException
     */
    protected function startTransaction()
    {
        if (false === $this->getClient() instanceof Client) {
            throw LogicException::create('The client must be instance of Client');
        }

        if (null === $this->getClient()->getContainer()) {
            throw LogicException::create('The client missing a container. Make sure the kernel was booted');
        }

        /** @var RegistryInterface $registry */
        $registry = $this->getClient()->getContainer()->get('doctrine');
        $managers = $registry->getManagers();

        /** @var  Connection $connection */
        foreach ($registry->getConnections() as $name => $connection) {
            if (isset($managers[$name]) && $managers[$name] instanceof EntityManagerInterface) {
                $managers[$name]->clear();
            }

            $connection->beginTransaction();
            self::$dbIsolationConnections[$name . '_' . uniqid()] = $connection;
        }
    }

    /**
     * Rollback transaction.
     */
    protected static function rollbackTransaction()
    {
        foreach (self::$dbIsolationConnections as $dbIsolationConnection) {
            if ($dbIsolationConnection->isConnected() && $dbIsolationConnection->isTransactionActive()) {
                $dbIsolationConnection->rollBack();
            }
        }

        self::$dbIsolationConnections = [];
    }
}
