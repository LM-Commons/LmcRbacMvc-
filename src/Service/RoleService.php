<?php

declare(strict_types=1);

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Lmc\Rbac\Mvc\Service;

use Laminas\Permissions\Rbac\RoleInterface;
use Lmc\Rbac\Exception;
use Lmc\Rbac\Identity\IdentityInterface;
use Lmc\Rbac\Mvc\Identity\IdentityProviderInterface;
use Lmc\Rbac\Mvc\Role\TraversalStrategyInterface;
use Lmc\Rbac\Service\RoleServiceInterface;

use function array_intersect;
use function array_unique;
use function count;

/**
 * Role service
 */
class RoleService
{
    protected IdentityProviderInterface $identityProvider;

    protected TraversalStrategyInterface $traversalStrategy;

    protected RoleServiceInterface $baseRoleService;

    /**
     * Constructor
     */
    public function __construct(
        IdentityProviderInterface $identityProvider,
        RoleServiceInterface $baseRoleService,
        TraversalStrategyInterface $traversalStrategy
    ) {
        $this->identityProvider  = $identityProvider;
        $this->baseRoleService   = $baseRoleService;
        $this->traversalStrategy = $traversalStrategy;
    }

    /**
     * Set the identity provider
     */
    public function setIdentityProvider(IdentityProviderInterface $identityProvider): void
    {
        $this->identityProvider = $identityProvider;
    }

    /**
     * Get the current identity from the identity provider
     */
    public function getIdentity(): ?IdentityInterface
    {
        return $this->identityProvider->getIdentity();
    }

    /**
     * Set the base role service
     */
    public function getRoleService(): RoleServiceInterface
    {
        return $this->baseRoleService;
    }

    /**
     * Get the identity roles from the current identity, applying some more logic
     *
     * @return RoleInterface[]
     * @throws Exception\RuntimeException
     */
    public function getIdentityRoles(): array
    {
        $identity = $this->getIdentity();

        return $this->baseRoleService->getIdentityRoles($identity);
    }

    /**
     * Check if the given roles match one of the identity roles
     *
     * This method is smart enough to automatically recursively extracts roles for hierarchical roles
     *
     * @param  string[]|RoleInterface[] $roles
     */
    public function matchIdentityRoles(array $roles): bool
    {
        $identityRoles = $this->getIdentityRoles();

        // Too easy...
        if (empty($identityRoles)) {
            return false;
        }

        $roleNames = [];

        foreach ($roles as $role) {
            $roleNames[] = $role instanceof RoleInterface ? $role->getName() : (string) $role;
        }

        $identityRoles = $this->flattenRoles($identityRoles);

        return count(array_intersect($roleNames, $identityRoles)) > 0;
    }

    /**
     * Flatten an array of roles with role names
     *
     * This method iterates through the list of roles, and convert any RoleInterface to a string. For any
     * role, it also extracts all the children
     *
     * @param  array|RoleInterface[] $roles
     * @return string[]
     */
    protected function flattenRoles(array $roles): array
    {
        $roleNames = [];
        $iterator  = $this->traversalStrategy->getRolesIterator($roles);

        foreach ($iterator as $role) {
            $roleNames[] = $role->getName();
        }

        return array_unique($roleNames);
    }
}
