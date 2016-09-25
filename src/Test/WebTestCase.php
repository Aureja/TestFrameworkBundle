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

use Aureja\Bundle\TestFrameworkBundle\DbIsolationTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 6/1/16 10:30 PM
 */
abstract class WebTestCase extends BaseWebTestCase
{
    use DbIsolationTrait;

    const DB_ISOLATION_ANNOTATION = 'dbIsolation';
    const DB_ISOLATION_PER_TEST_ANNOTATION = 'dbIsolationPerTest';

    /**
     * @var Client
     */
    private static $clientInstance;

    /**
     * @var bool[]
     */
    private static $dbIsolation;

    /**
     * @var bool[]
     */
    private static $dbIsolationPerTest;

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
    protected function tearDown()
    {
        if (self::hasDbIsolationPerTestAnnotation()) {
            $this->rollbackTransaction();
        }

        $refClass = new \ReflectionClass($this);
        foreach ($refClass->getProperties() as $prop) {
            if (false === $prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        if (self::hasDbIsolationAnnotation() || self::hasDbIsolationPerTestAnnotation()) {
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
        if ($force || self::hasDbIsolationPerTestAnnotation()) {
            $this->resetClient();
        }

        if (null === self::$clientInstance) {
            if (false === isset($options['debug'])) {
                $options['debug'] = false;
            }

            self::$clientInstance = static::createClient($options, $server);

            if (self::hasDbIsolationAnnotation() || self::hasDbIsolationPerTestAnnotation()) {
                $this->startTransaction();
            }
        }
    }

    /**
     * Reset client.
     */
    protected function resetClient()
    {
        if (self::$clientInstance) {
            if (self::hasDbIsolationAnnotation() || self::hasDbIsolationPerTestAnnotation()) {
                $this->rollbackTransaction();
            }

            self::$clientInstance = null;
        }
    }

    /**
     * @return null|ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getClient()->getContainer();
    }

    /**
     * Get value of dbIsolation option from annotation of called class.
     *
     * @return bool
     */
    private static function hasDbIsolationAnnotation()
    {
        $calledClass = get_called_class();
        if (false === isset(self::$dbIsolation[$calledClass])) {
            self::$dbIsolation[$calledClass] = self::hasAnnotation($calledClass, self::DB_ISOLATION_ANNOTATION);
        }

        return self::$dbIsolation[$calledClass];
    }

    /**
     * Get value of dbIsolationPerTest option from annotation of called class
     *
     * @return bool
     */
    private static function hasDbIsolationPerTestAnnotation()
    {
        $calledClass = get_called_class();
        if (!isset(self::$dbIsolationPerTest[$calledClass])) {
            self::$dbIsolationPerTest[$calledClass] = self::hasAnnotation(
                $calledClass,
                self::DB_ISOLATION_PER_TEST_ANNOTATION
            );
        }

        return self::$dbIsolationPerTest[$calledClass];
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
 