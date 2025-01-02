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

namespace LmcTest\Rbac\Mvc;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceManager;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Module;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\Module
 */
class ModuleTest extends TestCase
{
    public function testConfigIsArray()
    {
        $module = new Module();
        $this->assertIsArray($module->getConfig());
    }

    public function testCanRegisterGuards()
    {
        $module         = new Module();
        $mvcEvent       = $this->createMock(MvcEvent::class);
        $application    = $this->createMock(Application::class);
        $eventManager   = $this->createMock(EventManagerInterface::class);
        $serviceManager = $this->createMock(ServiceManager::class);

        $mvcEvent->expects($this->once())->method('getTarget')->willReturn($application);
        $application->expects($this->once())->method('getEventManager')->willReturn($eventManager);
        $application->expects($this->once())->method('getServiceManager')->willReturn($serviceManager);

        $guard = $this->createMock(GuardInterface::class);
        $guard->expects($this->once())->method('attach')->with($eventManager);

        $guards = [$guard];

        $serviceManager->expects($this->once())
                       ->method('get')
                       ->with('Lmc\Rbac\Mvc\Guards')
                       ->willReturn($guards);

        $module->onBootstrap($mvcEvent);
    }
}
