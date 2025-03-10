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

namespace Lmc\Rbac\Mvc\View\Strategy;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ViewModel;
use Lmc\Rbac\Mvc\Exception\UnauthorizedExceptionInterface;
use Lmc\Rbac\Mvc\Guard\GuardInterface;
use Lmc\Rbac\Mvc\Options\UnauthorizedStrategyOptions;

/**
 * This strategy renders a specific template when a user is unauthorized
 */
class UnauthorizedStrategy extends AbstractStrategy
{
    protected UnauthorizedStrategyOptions $options;

    /**
     * Constructor
     */
    public function __construct(UnauthorizedStrategyOptions $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function attach(EventManagerInterface $events, $priority = 1): void
    {
        // Temporary fix to priority to make sure listeners runs after MVC's exception handler
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError'], -1);
    }

    /**
     * @private
     */
    public function onError(MvcEvent $event): void
    {
        // Do nothing if no error or if response is not HTTP response
        if (
            ! $event->getParam('exception') instanceof UnauthorizedExceptionInterface
            || $event->getResult() instanceof HttpResponse
            || ! $event->getResponse() instanceof HttpResponse
        ) {
            return;
        }

        $model = new ViewModel();
        $model->setTemplate($this->options->getTemplate());

        switch ($event->getError()) {
            case GuardInterface::GUARD_UNAUTHORIZED:
                $model->setVariable('error', GuardInterface::GUARD_UNAUTHORIZED);
                break;

            default:
                break;
        }

        $response = $event->getResponse() ?: new HttpResponse();
        $response->setStatusCode(403);

        $event->setResponse($response);
        $event->setResult($model);
    }
}
