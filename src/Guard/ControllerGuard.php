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

use function in_array;
use function strtolower;

/**
 * A controller guard can protect a controller and a set of actions
 */
class ControllerGuard extends AbstractGuard
{
    use ProtectionPolicyTrait;

    /**
     * Event priority
     */
    public const EVENT_PRIORITY = -10;

    protected RoleService $roleService;

    /**
     * Controller guard rules
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
     * Set the rules
     *
     * A controller rule is made the following way:
     *
     * [
     *     'controller' => 'ControllerName',
     *     'actions'    => []/string
     *     'roles'      => []/string
     * ]
     */
    public function setRules(array $rules): void
    {
        $this->rules = [];

        foreach ($rules as $rule) {
            $controller = strtolower($rule['controller']);
            $actions    = isset($rule['actions']) ? (array) $rule['actions'] : [];
            $roles      = (array) $rule['roles'];

            if (empty($actions)) {
                $this->rules[$controller][0] = $roles;
                continue;
            }

            foreach ($actions as $action) {
                $this->rules[$controller][strtolower($action)] = $roles;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isGranted(MvcEvent $event): bool
    {
        $routeMatch = $event->getRouteMatch();
        $controller = strtolower($routeMatch->getParam('controller'));
        $action     = strtolower($routeMatch->getParam('action'));

        // If no rules apply, it is considered as granted or not based on the protection policy
        if (! isset($this->rules[$controller])) {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        // Algorithm is as follow: we first check if there is an exact match (controller + action), if not
        // we check if there are rules set globally for the whole controllers (see the index "0"), and finally
        // if nothing is matched, we fallback to the protection policy logic

        if (isset($this->rules[$controller][$action])) {
            $allowedRoles = $this->rules[$controller][$action];
        } elseif (isset($this->rules[$controller][0])) {
            $allowedRoles = $this->rules[$controller][0];
        } else {
            return $this->protectionPolicy === self::POLICY_ALLOW;
        }

        if (in_array('*', $allowedRoles)) {
            return true;
        }

        return $this->roleService->matchIdentityRoles($allowedRoles);
    }
}
