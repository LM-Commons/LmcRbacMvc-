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

use Laminas\ServiceManager\ServiceManager;
use Lmc\Rbac\Mvc\Guard\ControllerGuard;
use Lmc\Rbac\Mvc\Guard\ControllerPermissionsGuard;
use Lmc\Rbac\Mvc\Guard\GuardPluginManager;
use Lmc\Rbac\Mvc\Guard\GuardsFactory;
use Lmc\Rbac\Mvc\Guard\RouteGuard;
use Lmc\Rbac\Mvc\Guard\RoutePermissionsGuard;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Service\AuthorizationService;
use Lmc\Rbac\Mvc\Service\AuthorizationServiceInterface;
use Lmc\Rbac\Mvc\Service\RoleService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\Guard\GuardsFactory
 */
class GuardsFactoryTest extends TestCase
{
    public function testFactory()
    {
        $moduleOptions = new ModuleOptions([
            'guards' => [
                RouteGuard::class                 => [
                    'admin/*' => 'role1',
                ],
                RoutePermissionsGuard::class      => [
                    'admin/post' => 'post.manage',
                ],
                ControllerGuard::class            => [
                    [
                        'controller' => 'MyController',
                        'actions'    => ['index', 'edit'],
                        'roles'      => ['role'],
                    ],
                ],
                ControllerPermissionsGuard::class => [
                    [
                        'controller'  => 'PostController',
                        'actions'     => ['index', 'edit'],
                        'permissions' => ['post.read'],
                    ],
                ],
            ],
        ]);

        $serviceManager = new ServiceManager();
        $pluginManager  = new GuardPluginManager($serviceManager);

        $serviceManager->setService(ModuleOptions::class, $moduleOptions);
        $serviceManager->setService(GuardPluginManager::class, $pluginManager);
        $serviceManager->setService(
            RoleService::class,
            $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock()
        );
        $serviceManager->setService(
            AuthorizationService::class,
            $this->getMockBuilder(AuthorizationServiceInterface::class)->disableOriginalConstructor()->getMock()
        );

        $factory = new GuardsFactory();
        $guards  = $factory($serviceManager, '');

        $this->assertIsArray($guards);

        $this->assertCount(4, $guards);
        $this->assertInstanceOf(RouteGuard::class, $guards[0]);
        $this->assertInstanceOf(RoutePermissionsGuard::class, $guards[1]);
        $this->assertInstanceOf(ControllerGuard::class, $guards[2]);
        $this->assertInstanceOf(ControllerPermissionsGuard::class, $guards[3]);
    }

    public function testReturnArrayIfNoConfig()
    {
        $moduleOptions = new ModuleOptions([
            'guards' => [],
        ]);

        $serviceManager = new ServiceManager();
        $pluginManager  = new GuardPluginManager($serviceManager);

        $serviceManager->setService(ModuleOptions::class, $moduleOptions);

        $factory = new GuardsFactory();
        $guards  = $factory($serviceManager, GuardsFactory::class);

        $this->assertIsArray($guards);

        $this->assertEmpty($guards);
    }
}
