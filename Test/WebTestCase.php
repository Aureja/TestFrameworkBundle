<?php

/*
 * This file is part of the Aureja package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aureja\Bundle\TestFrameworkBundle\Test;

use Aureja\Bundle\TestFrameworkBundle\Exception\LogicException;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 6/1/16 10:30 PM
 */
abstract class WebTestCase extends BaseWebTestCase
{
    const DB_ISOLATION_ANNOTATION = 'dbIsolation';

    /**
     * @var Client
     */
    private static $clientInstance;

    /**
     * @var bool[]
     */
    private static $dbIsolation;

    /**
     * @var Connection[]
     */
    private static $connections = [];

    /**
     * @return Client
     */
    protected function getClient()
    {
        return self::$clientInstance;
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        if (self::getDbIsolation()) {
            self::rollbackTransaction();
        }

        self::$clientInstance = null;
    }

    /**
     * Creates a Client.
     *
     * @param array $options An array of options to pass to the createKernel class
     * @param array $server  An array of server parameters
     * @param bool  $force If this option - true, will reset client on each initClient call
     *
     * @return Client A Client instance
     */
    protected function initClient(array $options = [], array $server = [], $force = false)
    {
        if ($force) {
            $this->resetClient();
        }

        if (null === self::$clientInstance) {
            if (false === isset($options['debug'])) {
                $options['debug'] = false;
            }

            self::$clientInstance = static::createClient($options, $server);

            if (self::getDbIsolation()) {
                $this->startTransaction();
            }
        }
    }

    protected function resetClient()
    {
        if (self::$clientInstance) {
            if (self::getDbIsolation()) {
                $this->rollbackTransaction();
            }

            self::$clientInstance = null;
        }
    }

    /**
     * Start transaction.
     *
     * @throws LogicException
     */
    protected function startTransaction()
    {
        if (false === self::$clientInstance instanceof Client) {
            throw LogicException::create('The client must be instance of Client');
        }

        if (null === self::$clientInstance->getContainer()) {
            throw LogicException::create('The client missing a container. Make sure the kernel was booted');
        }

        /** @var RegistryInterface $registry */
        $registry = self::$clientInstance->getContainer()->get('doctrine');

        foreach ($registry->getManagers() as $name => $em) {
            if ($em instanceof EntityManagerInterface) {
                $em->clear();
                $em->getConnection()->beginTransaction();

                self::$connections[$name . '_' . uniqid()] = $em->getConnection();
            }
        }
    }

    /**
     * Rollback transaction.
     */
    protected static function rollbackTransaction()
    {
        foreach (self::$connections as $connection) {
            while ($connection->isConnected() && $connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }

        self::$connections = [];
    }

    /**
     * Get value of dbIsolation option from annotation of called class.
     *
     * @return bool
     */
    private static function getDbIsolation()
    {
        $calledClass = get_called_class();

        if (false === isset(self::$dbIsolation[$calledClass])) {
            self::$dbIsolation[$calledClass] = self::hasAnnotation($calledClass, self::DB_ISOLATION_ANNOTATION);
        }

        return self::$dbIsolation[$calledClass];
    }

    /**
     * @param string $className
     * @param string $annotationName
     *
     * @return bool
     */
    private static function hasAnnotation($className, $annotationName)
    {
        $annotations = \PHPUnit_Util_Test::parseTestMethodAnnotations($className);

        return isset($annotations['class'][$annotationName]);
    }
}
 