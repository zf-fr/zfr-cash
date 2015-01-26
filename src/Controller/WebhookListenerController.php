<?php
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

namespace ZfrCash\Controller;

use Zend\Http\Response as HttpResponse;
use Zend\Mvc\Controller\AbstractActionController;
use ZfrCash\Event\WebhookEvent;
use ZfrCash\Exception\RuntimeException;
use ZfrCash\Options\ModuleOptions;
use ZfrStripe\Client\StripeClient;
use ZfrStripe\Exception\NotFoundException as StripeNotFoundException;

/**
 * Listen to Stripe webhooks
 *
 * You can write routes that will listen to both live and test events. It will automatically trigger
 * an event that you can listen to and do appropriate actions based on the received event. ZfrCash comes
 * with some basic actions to common events to keep the system synchronized
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class WebhookListenerController extends AbstractActionController
{
    /**
     * @var StripeClient
     */
    protected $stripeClient;

    /**
     * @var ModuleOptions
     */
    protected $moduleOptions;

    /**
     * @param StripeClient  $stripeClient
     * @param ModuleOptions $moduleOptions
     */
    public function __construct(StripeClient $stripeClient, ModuleOptions $moduleOptions)
    {
        $this->stripeClient  = $stripeClient;
        $this->moduleOptions = $moduleOptions;
    }

    /**
     * @return HttpResponse
     */
    public function handleLiveEventAction()
    {
        $event = json_decode($this->request->getContent(), true);

        if (null === $event || ($event['livemode'] && !$this->isLiveStripeKey())) {
            return new HttpResponse(); // Return silently
        }

        return $this->handleEvent($event);
    }

    /**
     * @return HttpResponse
     */
    public function handleTestEventAction()
    {
        $event = json_decode($this->request->getContent(), true);

        if (null === $event || (!$event['livemode'] && $this->isLiveStripeKey())) {
            return new HttpResponse(); // Return silently
        }

        return $this->handleEvent($event);
    }

    /**
     * @param  array $stripeEvent
     * @return HttpResponse
     */
    public function handleEvent(array $stripeEvent)
    {
        $response = new HttpResponse();

        if ($this->moduleOptions->getValidateWebhooks()) {
            try {
                $stripeEvent = $this->stripeClient->getEvent(['id' => $stripeEvent['id']]);
            } catch (StripeNotFoundException $exception) {
                // If the event is not found, then we simply return silently. This typically only happen
                // with someone is trying to hack your system with scam events
                return $response;
            }
        }

        $event              = new WebhookEvent($stripeEvent);
        $responseCollection = $this->getEventManager()->trigger(WebhookEvent::WEBHOOK_RECEIVED, $event);

        $message = '';

        foreach ($responseCollection as $singleResponse) {
            if (is_string($singleResponse)) {
                $message .= "$singleResponse\n";
            }
        }

        $response->setContent(sprintf(
            'Stripe event "%s" of type "%s" was received and processed. Logged message(s): %s',
            $stripeEvent['id'],
            $stripeEvent['type'],
            $message
        ));

        return $response;
    }

    /**
     * Detect if the key configured with the Stripe client is a live key or not
     *
     * @return bool
     */
    protected function isLiveStripeKey()
    {
        return substr($this->stripeClient->getApiKey(), 3, 4) === 'live';
    }
}
