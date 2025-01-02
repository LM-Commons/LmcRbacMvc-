<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\Role;

use Lmc\Rbac\Mvc\Role\RecursiveRoleIteratorStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Traversable;

#[CoversClass(RecursiveRoleIteratorStrategy::class)]
class RecursiveRoleIteratorStrategyTest extends TestCase
{
    public function testGetRecursiveRoleIterator(): void
    {
        $roles = [];

        $strategy = new RecursiveRoleIteratorStrategy();
        $iterator = $strategy->getRolesIterator($roles);
        $this->assertInstanceOf(Traversable::class, $iterator);
    }
}
