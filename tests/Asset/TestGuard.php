<?php

declare(strict_types=1);

namespace LmcTest\Rbac\Mvc\Asset;

use Laminas\Mvc\MvcEvent;
use Lmc\Rbac\Mvc\Guard\AbstractGuard;

class TestGuard extends AbstractGuard
{
    /**
     * @inheritDoc
     */
    public function isGranted(MvcEvent $event): bool
    {
        return true;
    }
}
