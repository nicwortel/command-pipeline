<?php

declare(strict_types=1);

namespace NicWortel\CommandPipeline\Tests\System\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class TestEntity
{
    /**
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    public int $id;

    /**
     *
     * @ORM\Column(type="string")
     */
    public string $emailAddress;
}
