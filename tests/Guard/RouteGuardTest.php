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

namespace LmcTest\Rbac\Mvc\Guard;

use ArrayIterator;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Router\RouteMatch;
use Lmc\Rbac\Identity\IdentityInterface;
use Lmc\Rbac\Mvc\Exception\UnauthorizedException;
use Lmc\Rbac\Mvc\Guard\AbstractGuard;
use Lmc\Rbac\Mvc\Guard\ControllerGuard;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Guard\RouteGuard;
use Lmc\Rbac\Mvc\Guard\RoutePermissionsGuard;
use Lmc\Rbac\Mvc\Identity\IdentityProviderInterface;
use Lmc\Rbac\Mvc\Role\RecursiveRoleIteratorStrategy;
use Lmc\Rbac\Mvc\Service\RoleService;
use Lmc\Rbac\Role\InMemoryRoleProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(AbstractGuard::class)]
#[CoversClass(RouteGuard::class)]
class RouteGuardTest extends TestCase
{
    public function testAttachToRightEvent()
    {
        $guard = new RouteGuard($this->createMock(RoleService::class));

        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManager->expects($this->once())
                     ->method('attach')
                     ->with(RouteGuard::EVENT_NAME);

        $guard->attach($eventManager);
    }

    /**
     * We want to ensure an order for guards
     */
    public function testAssertRouteGuardPriority()
    {
        $this->assertGreaterThan(RoutePermissionsGuard::EVENT_PRIORITY, RouteGuard::EVENT_PRIORITY);
        $this->assertGreaterThan(ControllerGuard::EVENT_PRIORITY, RouteGuard::EVENT_PRIORITY);
    }

    public static function rulesConversionProvider(): array
    {
        return [
            // Simple string to array conversion
            [
                'rules'    => [
                    'route' => 'role1',
                ],
                'expected' => [
                    'route' => ['role1'],
                ],
            ],

            // Array to array
            [
                'rules'    => [
                    'route' => ['role1', 'role2'],
                ],
                'expected' => [
                    'route' => ['role1', 'role2'],
                ],
            ],

            // Traversable to array
            [
                'rules'    => [
                    'route' => new ArrayIterator(['role1', 'role2']),
                ],
                'expected' => [
                    'route' => ['role1', 'role2'],
                ],
            ],

            // Block a route for everyone
            [
                'rules'    => [
                    'route',
                ],
                'expected' => [
                    'route' => [],
                ],
            ],
        ];
    }

    #[DataProvider('rulesConversionProvider')]
    public function testRulesConversions(array $rules, array $expected)
    {
        $roleService = $this->createMock(RoleService::class);
        $routeGuard  = new RouteGuard($roleService, $rules);

        $reflProperty = new ReflectionProperty($routeGuard, 'rules');
        $reflProperty->setAccessible(true);

        $this->assertEquals($expected, $reflProperty->getValue($routeGuard));
    }

    public static function routeDataProvider(): array
    {
        return [
            // Assert basic one-to-one mapping with both policies
            [
                'rules'            => ['adminRoute' => 'admin'],
                'matchedRouteName' => 'adminRoute',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['adminRoute' => 'admin'],
                'matchedRouteName' => 'adminRoute',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that policy changes result for non-specified route guards
            [
                'rules'            => ['route' => 'member'],
                'matchedRouteName' => 'anotherRoute',
                'rolesConfig'      => ['member'],
                'identityRole'     => ['member'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['route' => 'member'],
                'matchedRouteName' => 'anotherRoute',
                'rolesConfig'      => ['member'],
                'identityRole'     => ['member'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that composed route work for both policies
            [
                'rules'            => ['admin/dashboard' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['admin/dashboard' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that wildcard route work for both policies
            [
                'rules'            => ['admin/*' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['admin/*' => 'admin'],
                'matchedRouteName' => 'admin/dashboard',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that wildcard route does match (or not depending on the policy)
            // if rules is after matched route name
            [
                'rules'            => ['fooBar/*' => 'admin'],
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['fooBar/*' => 'admin'],
                'matchedRouteName' => 'admin/fooBar/baz',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that it can grant access with multiple rules
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin',
                ],
                'matchedRouteName' => 'route1',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin',
                ],
                'matchedRouteName' => 'route1',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert that it can grant/deny access with multiple rules based on the policy
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin',
                ],
                'matchedRouteName' => 'route3',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => [
                    'route1' => 'admin',
                    'route2' => 'admin',
                ],
                'matchedRouteName' => 'route3',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert it can deny access if a role does not have access
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesConfig'      => ['admin', 'guest'],
                'identityRole'     => ['guest'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesConfig'      => ['admin', 'guest'],
                'identityRole'     => ['guest'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert it can grant access using child-parent relationship between roles
            [
                'rules'            => ['home' => 'guest'],
                'matchedRouteName' => 'home',
                'rolesConfig'      => [
                    'admin'  => [
                        'children' => ['member'],
                    ],
                    'member' => [
                        'children' => ['guest'],
                    ],
                    'guest',
                ],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['home' => 'guest'],
                'matchedRouteName' => 'home',
                'rolesConfig'      => [
                    'admin'  => [
                        'children' => ['member'],
                    ],
                    'member' => [
                        'children' => ['guest'],
                    ],
                    'guest',
                ],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert it can deny access using child-parent relationship between roles (just to be sure)
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesConfig'      => [
                    'admin' => [
                        'children' => 'member',
                    ],
                    'member',
                ],
                'identityRole'     => ['member'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['route' => 'admin'],
                'matchedRouteName' => 'route',
                'rolesConfig'      => [
                    'admin' => [
                        'children' => 'member',
                    ],
                    'member',
                ],
                'identityRole'     => ['member'],
                'isGranted'        => false,
                'policy'           => GuardInterface::POLICY_DENY,
            ],

            // Assert wildcard in role
            [
                'rules'            => ['home' => '*'],
                'matchedRouteName' => 'home',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_ALLOW,
            ],
            [
                'rules'            => ['home' => '*'],
                'matchedRouteName' => 'home',
                'rolesConfig'      => ['admin'],
                'identityRole'     => ['admin'],
                'isGranted'        => true,
                'policy'           => GuardInterface::POLICY_DENY,
            ],
        ];
    }

    #[DataProvider('routeDataProvider')]
    public function testRouteGranted(
        array $rules,
        string $matchedRouteName,
        array $rolesConfig,
        array $identityRole,
        bool $isGranted,
        string $policy
    ) {
        $event      = new MvcEvent();
        $routeMatch = $this->createRouteMatch();
        $routeMatch->setMatchedRouteName($matchedRouteName);

        $event->setRouteMatch($routeMatch);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->any())->method('getRoles')->willReturn($identityRole);

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->any())->method('getIdentity')->willReturn($identity);

        $roleProvider = new InMemoryRoleProvider($rolesConfig);
        $roleService  = new RoleService(
            $identityProvider,
            new \Lmc\Rbac\Service\RoleService($roleProvider, 'guest'),
            new RecursiveRoleIteratorStrategy()
        );

        $routeGuard = new RouteGuard($roleService, $rules);
        $routeGuard->setProtectionPolicy($policy);

        $this->assertEquals($isGranted, $routeGuard->isGranted($event));
    }

    public function testProperlyFillEventOnAuthorization()
    {
        $event      = new MvcEvent();
        $routeMatch = $this->createRouteMatch();

        $application  = $this->createMock(Application::class);
        $eventManager = $this->createMock(EventManagerInterface::class);

        $application->expects($this->never())->method('getEventManager')->willReturn($eventManager);

        $routeMatch->setMatchedRouteName('adminRoute');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->any())->method('getRoles')->willReturn(['member']);

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->any())->method('getIdentity')->willReturn($identity);

        $roleProvider = new InMemoryRoleProvider(['member']);
        $roleService  = new RoleService(
            $identityProvider,
            new \Lmc\Rbac\Service\RoleService($roleProvider, 'guest'),
            new RecursiveRoleIteratorStrategy()
        );

        $routeGuard = new RouteGuard($roleService, [
            'adminRoute' => 'member',
        ]);

        $routeGuard->onResult($event);

        $this->assertEmpty($event->getError());
        $this->assertNull($event->getParam('exception'));
    }

    public function testProperlySetUnauthorizedAndTriggerEventOnUnauthorized()
    {
        $event      = new MvcEvent();
        $routeMatch = $this->createRouteMatch();

        $application  = $this->createMock(Application::class);
        $eventManager = $this->createMock(EventManager::class);

        $application->expects($this->once())->method('getEventManager')->willReturn($eventManager);

        $eventManager->expects($this->once())->method('triggerEvent')->with($event);

        $routeMatch->setMatchedRouteName('adminRoute');
        $event->setRouteMatch($routeMatch);
        $event->setApplication($application);

        $identity = $this->createMock(IdentityInterface::class);
        $identity->expects($this->any())->method('getRoles')->willReturn(['member']);

        $identityProvider = $this->createMock(IdentityProviderInterface::class);
        $identityProvider->expects($this->any())->method('getIdentity')->willReturn($identity);

        $roleProvider = new InMemoryRoleProvider(['member', 'guest']);
        $roleService  = new RoleService(
            $identityProvider,
            new \Lmc\Rbac\Service\RoleService($roleProvider, 'guest'),
            new RecursiveRoleIteratorStrategy()
        );

        $routeGuard = new RouteGuard($roleService, [
            'adminRoute' => 'guest',
        ]);

        $routeGuard->onResult($event);

        $this->assertTrue($event->propagationIsStopped());
        $this->assertEquals(RouteGuard::GUARD_UNAUTHORIZED, $event->getError());
        $this->assertInstanceOf(UnauthorizedException::class, $event->getParam('exception'));
    }

    public function createRouteMatch(array $params = []): RouteMatch
    {
        return new RouteMatch($params);
    }
}
