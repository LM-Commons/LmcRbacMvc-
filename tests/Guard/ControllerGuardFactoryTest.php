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
use Lmc\Rbac\Mvc\Guard\ControllerGuardFactory;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Guard\GuardPluginManager;
use Lmc\Rbac\Mvc\Options\ModuleOptions;
use Lmc\Rbac\Mvc\Service\RoleService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\Guard\ControllerGuardFactory
 */
class ControllerGuardFactoryTest extends TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $options = new ModuleOptions([
            'identity_provider' => 'Lmc\Rbac\Mvc\Identity\AuthenticationProvider',
            'guards'            => [
                ControllerGuard::class => [
                    'controller' => 'MyController',
                    'actions'    => 'edit',
                    'roles'      => 'member',
                ],
            ],
            'protection_policy' => GuardInterface::POLICY_ALLOW,
        ]);

        $serviceManager->setService(ModuleOptions::class, $options);
        $serviceManager->setService(
            RoleService::class,
            $this->getMockBuilder(RoleService::class)->disableOriginalConstructor()->getMock()
        );

        $factory         = new ControllerGuardFactory();
        $controllerGuard = $factory($serviceManager, GuardPluginManager::class);

        $this->assertInstanceOf(ControllerGuard::class, $controllerGuard);
        $this->assertEquals(GuardInterface::POLICY_ALLOW, $controllerGuard->getProtectionPolicy());
    }
}
