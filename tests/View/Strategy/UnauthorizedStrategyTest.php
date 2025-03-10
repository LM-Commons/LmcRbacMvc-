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

namespace LmcTest\Rbac\Mvc\View\Strategy;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ModelInterface;
use Laminas\View\Model\ViewModel;
use Lmc\Rbac\Mvc\Exception\UnauthorizedException;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Options\UnauthorizedStrategyOptions;
use Lmc\Rbac\Mvc\View\Strategy\UnauthorizedStrategy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Lmc\Rbac\Mvc\View\Strategy\UnauthorizedStrategy
 * @covers \Lmc\Rbac\Mvc\View\Strategy\AbstractStrategy
 */
class UnauthorizedStrategyTest extends TestCase
{
    public function testAttachToRightEvent()
    {
        $strategyListener = new UnauthorizedStrategy(new UnauthorizedStrategyOptions());

        $eventManager = $this->createMock(EventManagerInterface::class);
        $eventManager->expects($this->once())
                     ->method('attach')
                     ->with(MvcEvent::EVENT_DISPATCH_ERROR);

        $strategyListener->attach($eventManager);
    }

    public function testFillEvent()
    {
        $response = new HttpResponse();

        $mvcEvent = new MvcEvent();
        $mvcEvent->setParam('exception', new UnauthorizedException());
        $mvcEvent->setResponse($response);

        $options = new UnauthorizedStrategyOptions([
            'template' => 'error/403',
        ]);

        $unauthorizedStrategy = new UnauthorizedStrategy($options);

        $unauthorizedStrategy->onError($mvcEvent);

        $this->assertEquals(403, $mvcEvent->getResponse()->getStatusCode());
        $this->assertInstanceOf(ModelInterface::class, $mvcEvent->getResult());
    }

    public function testNoErrorEvent()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setResponse(new HttpResponse());
        $mvcEvent->setParam('exception', null);

        $options = new UnauthorizedStrategyOptions([
            'template' => 'error/403',
        ]);

        $unauthorizedStrategy = new UnauthorizedStrategy($options);

        $unauthorizedStrategy->onError($mvcEvent);
        $this->assertEquals(200, $mvcEvent->getResponse()->getStatusCode());
    }

    public function testResultIsResponseEvent()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setResponse(new HttpResponse());
        $mvcEvent->setParam('exception', null);

        $options = new UnauthorizedStrategyOptions([
            'template' => 'error/403',
        ]);

        $unauthorizedStrategy = new UnauthorizedStrategy($options);

        $unauthorizedStrategy->onError($mvcEvent);
        $this->assertEquals(200, $mvcEvent->getResponse()->getStatusCode());
    }

    public function testGuardInterfaceErrorEvent()
    {
        $mvcEvent = new MvcEvent();
        $mvcEvent->setResponse(new HttpResponse());
        $mvcEvent->setParam('exception', new UnauthorizedException());
        $mvcEvent->setParam('error', GuardInterface::GUARD_UNAUTHORIZED);

        $options = new UnauthorizedStrategyOptions([
            'template' => 'error/403',
        ]);

        $unauthorizedStrategy = new UnauthorizedStrategy($options);

        $unauthorizedStrategy->onError($mvcEvent);
        $this->assertEquals(403, $mvcEvent->getResponse()->getStatusCode());
        $this->assertInstanceOf(ModelInterface::class, $mvcEvent->getResult());
        /** @var ViewModel $model */
        $model = $mvcEvent->getResult();
        $this->assertEquals(GuardInterface::GUARD_UNAUTHORIZED, $model->getVariable('error'));
    }
}
