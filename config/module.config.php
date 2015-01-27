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

use Doctrine\ORM\Mapping\Driver\XmlDriver;
use ZfrCash\Controller\WebhookListenerController;
use ZfrCash\Factory\CardServiceFactory;
use ZfrCash\Factory\CustomerDiscountServiceFactory;
use ZfrCash\Factory\CustomerServiceFactory;
use ZfrCash\Factory\ModuleOptionsFactory;
use ZfrCash\Factory\PlanServiceFactory;
use ZfrCash\Factory\SubscriptionDiscountServiceFactory;
use ZfrCash\Factory\SubscriptionServiceFactory;
use ZfrCash\Factory\WebhookListenerControllerFactory;
use ZfrCash\Factory\WebhookListenerFactory;
use ZfrCash\Listener\WebhookListener;
use ZfrCash\Options\ModuleOptions;
use ZfrCash\Service\CardService;
use ZfrCash\Service\CustomerDiscountService;
use ZfrCash\Service\CustomerService;
use ZfrCash\Service\PlanService;
use ZfrCash\Service\SubscriptionDiscountService;
use ZfrCash\Service\SubscriptionService;
use ZfrCash\Validator\ViesValidator;

return [
    /**
     * --------------------------------------------------------------------------------
     * SERVICE MANAGER CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'service_manager' => [
        'factories' => [
            CardService::class                 => CardServiceFactory::class,
            CustomerDiscountService::class     => CustomerDiscountServiceFactory::class,
            CustomerService::class             => CustomerServiceFactory::class,
            ModuleOptions::class               => ModuleOptionsFactory::class,
            PlanService::class                 => PlanServiceFactory::class,
            SubscriptionDiscountService::class => SubscriptionDiscountServiceFactory::class,
            SubscriptionService::class         => SubscriptionServiceFactory::class,
            WebhookListener::class             => WebhookListenerFactory::class
        ]
    ],

    /**
     * --------------------------------------------------------------------------------
     * DOCTRINE CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'doctrine' => [
        'driver' => [
            'zfr_cash_driver' => [
                'class' => XmlDriver::class,
                'paths' => __DIR__ . '/doctrine',
            ],
            'orm_default' => [
                'drivers' => [
                    'ZfrCash\Entity' => 'zfr_cash_driver',
                ],
            ],
        ],
    ],

    /**
     * --------------------------------------------------------------------------------
     * ROUTER CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'router' => [
        'routes' => [
            'stripe-webhook' => [
                'type'    => 'Literal',
                'options' => [
                    'route'    => '/stripe',
                    'defaults' => [
                        'controller' => WebhookListenerController::class
                    ]
                ],
                'child_routes' => [
                    'test-listener' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/test-listener',
                            'defaults' => [
                                'action' => 'handleTestEvent'
                            ]
                        ]
                    ],

                    'live-listener' => [
                        'type'    => 'Literal',
                        'options' => [
                            'route'    => '/live-listener',
                            'defaults' => [
                                'action' => 'handleLiveEvent'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],

    /**
     * --------------------------------------------------------------------------------
     * CONTROLLERS CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'controllers' => [
        'factories' => [
            WebhookListenerController::class => WebhookListenerControllerFactory::class
        ]
    ],

    /**
     * --------------------------------------------------------------------------------
     * VALIDATORS CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'validators' => [
        'invokables' => [
            ViesValidator::class => ViesValidator::class
        ]
    ],

    /**
     * --------------------------------------------------------------------------------
     * ZFR CASH CONFIGURATION
     * --------------------------------------------------------------------------------
     */

    'zfr_cash' => [
        // Object manager key
        'object_manager' => 'doctrine.entitymanager.orm_default',

        // Define if webhooks coming from Stripe should be validated (recommended)
        'validate_webhooks' => true,

        // Register built-in listeners (recommended)
        'register_listeners' => true
    ]
];
