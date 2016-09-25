<?php

/*
 * This file is part of the Aureja package.
 *
 * (c) Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Aureja\Bundle\TestFrameworkBundle\Tests\Functional\Test;

use Aureja\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Aureja\Bundle\TestFrameworkBundle\Tests\App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;


/**
 * @author Tadas Gliaubicas <tadcka89@gmail.com>
 *
 * @since 7/26/16 8:23 PM
 *
 * @dbIsolationPerTest
 */
class DbIsolationPerTestTest extends WebTestCase
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }

    public function testInsertData()
    {
        $this->createSchema();

        $user = new User();
        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals(1, count($this->em->getRepository(User::class)->findAll()));
    }

    public function testRollback()
    {
        $this->createSchema();

        $user = new User();
        $this->em->persist($user);
        $this->em->flush();

        $this->assertEquals(1, count($this->em->getRepository(User::class)->findAll()));
    }

    private function createSchema()
    {
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->createSchema(
            [
                $this->em->getClassMetadata(User::class),
            ]
        );
    }
}
 