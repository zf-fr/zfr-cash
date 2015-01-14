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
use ZfrCash\Factory\CardServiceFactory;
use ZfrCash\Factory\CustomerServiceFactory;
use ZfrCash\Factory\DiscountServiceFactory;
use ZfrCash\Factory\InvoiceServiceFactory;
use ZfrCash\Factory\ModuleOptionsFactory;
use ZfrCash\Factory\PlanServiceFactory;
use ZfrCash\Factory\SubscriptionServiceFactory;
use ZfrCash\Factory\VatServiceFactory;
use ZfrCash\Factory\WebhookListenerFactory;
use ZfrCash\Listener\WebhookListener;
use ZfrCash\Options\ModuleOptions;
use ZfrCash\Service\CardService;
use ZfrCash\Service\CustomerService;
use ZfrCash\Service\DiscountService;
use ZfrCash\Service\InvoiceService;
use ZfrCash\Service\PlanService;
use ZfrCash\Service\SubscriptionService;
use ZfrCash\Service\VatService;
use ZfrCash\Validator\ViesValidator;

return [
    'service_manager' => [
        'factories' => [
            CardService::class         => CardServiceFactory::class,
            CustomerService::class     => CustomerServiceFactory::class,
            DiscountService::class     => DiscountServiceFactory::class,
            InvoiceService::class      => InvoiceServiceFactory::class,
            ModuleOptions::class       => ModuleOptionsFactory::class,
            PlanService::class         => PlanServiceFactory::class,
            SubscriptionService::class => SubscriptionServiceFactory::class,
            VatService::class          => VatServiceFactory::class,
            WebhookListener::class     => WebhookListenerFactory::class
        ]
    ],

    'validators' => [
        'invokables' => [
            'vies' => ViesValidator::class
        ]
    ],

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

    'zfr_cash' => [
        // Object manager key
        'object_manager' => 'doctrine.entitymanager.orm_default',

        // Define if webhooks coming from Stripe should be validated (recommended)
        'validate_webhooks' => true,

        // Register built-in listeners
        'register_listeners' => true
    ]
];
