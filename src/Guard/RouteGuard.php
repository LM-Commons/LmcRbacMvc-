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

namespace Lmc\Rbac\Mvc\Guard;

use Laminas\Mvc\MvcEvent;
use Lmc\Rbac\Mvc\Service\RoleService;

use function array_keys;
use function fnmatch;
use function in_array;
use function is_int;

use const FNM_CASEFOLD;

/**
 * A route guard can protect a route or a hierarchy of routes (using simple wildcard pattern)
 */
class RouteGuard extends AbstractGuard
{
    use ProtectionPolicyTrait;

    protected RoleService $roleService;

    /**
     * Route guard rules
     *
     * Those rules are an associative array that map a rule with one or multiple roles
     */
    protected array $rules = [];

    /**
     * Constructor
     */
    public function __construct(RoleService $roleService, array $rules = [])
    {
        $this->roleService = $roleService;
        $this->setRules($rules);
    }

    /**
     * Set the rules (it overrides any existing rules)
     */
    public function setRules(array $rules): void
    {
        $this->rules = [];

        foreach ($rules as $key => $value) {
            if (is_int($key)) {
                $routeRegex = $value;
                $roles      = [];
            } else {
                $routeRegex = $key;
                $roles      = (array) $value;
            }

            $this->rules[$routeRegex] = $roles;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event): bool
    {
        $matchedRouteName = $event->getRouteMatch()->getMatchedRouteName();
        $allowedRoles     = null;

        foreach (array_keys($this->rules) as $routeRule) {
            if (fnmatch($routeRule, $matchedRouteName, FNM_CASEFOLD)) {
                $allowedRoles = $this->rules[$routeRule];
                break;
            }
        }

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (null === $allowedRoles) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        if (in_array('*', $allowedRoles)) {
            return true;
        }

        return $this->roleService->matchIdentityRoles($allowedRoles);
    }
}
