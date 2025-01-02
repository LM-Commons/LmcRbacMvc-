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
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Guard\RoutePermissionsGuard;
use Lmc\Rbac\Mvc\Guard\RoutePermissionsGuardFactory;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Service\AuthorizationService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\Guard\RoutePermissionsGuardFactory
 */
class RoutePermissionsGuardFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $creationOptions = [
            'route' => 'role',
        ];

        $options = new ModuleOptions([
            'identity_provider' => 'Lmc\Rbac\Mvc\Identity\AuthenticationProvider',
            'guards'            => [
                RoutePermissionsGuard::class => $creationOptions,
            ],
            'protection_policy' => GuardInterface::POLICY_ALLOW,
        ]);

        $serviceManager->setService(ModuleOptions::class, $options);
        $serviceManager->setService(
            AuthorizationService::class,
            $this->getMockBuilder(AuthorizationService::class)->disableOriginalConstructor()->getMock()
        );

        $factory    = new RoutePermissionsGuardFactory();
        $routeGuard = $factory($serviceManager, RoutePermissionsGuard::class);

        $this->assertInstanceOf(RoutePermissionsGuard::class, $routeGuard);
        $this->assertEquals(GuardInterface::POLICY_ALLOW, $routeGuard->getProtectionPolicy());
    }
}
