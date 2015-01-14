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

namespace ZfrCash\Service;

use Zend\Http\Client;
use Zend\Http\Request;
use ZfrCash\Entity\VatCustomerInterface;
use ZfrCash\Options\ModuleOptions;

/**
 * This service automatically get the VAT rate for a Stripe customer
 *
 * It uses the Octobat service to do that
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class VatService 
{
    /**
     * @var ModuleOptions
     */
    private $moduleOptions;

    /**
     * Get the VAT rate for the given customer, based on its VAT info and your VAT info
     *
     * @param  VatCustomerInterface $customer
     * @return float
     */
    public function getVatRate(VatCustomerInterface $customer)
    {
        list($vatCountry, $vatNumber) = $this->extractVatParametersFromCustomer($customer);

        // If no VAT country can be found, no VAT is applied
        if (null === $vatCountry) {
            return 0;
        }

        $parameters = [
            'supplier' => [
                'country'    => $this->moduleOptions->getVatCountry(),
                'vat_number' => $this->moduleOptions->getVatNumber()
            ],

            'customer' => [
                'country'    => $vatCountry,
                'vat_number' => $vatNumber
            ],

            'transaction' => [
                'type'     => $vatNumber ? 'B2B' : 'B2C',
                'eservice' => true
            ]
        ];

        $httpClient = new Client('http://vatmoss.octobat.com/vat.json');

        $httpClient->getRequest()
                   ->getHeaders()
                   ->addHeaderLine('Content-Type', 'application/json');

        $response = $httpClient->setMethod(Request::METHOD_POST)
                               ->setRawBody(json_encode($parameters))
                               ->send();

        // @TODO: this is not ideal, because if Octobat fails, we return 0, I think we should have a rough
        // fallback implemented server-side
        if (!$response->isOk()) {
            return 0;
        }

        return json_decode($response->getBody(), true)['vat_rate'];
    }

    /**
     * @param  VatCustomerInterface $customer
     * @return array VAT country first, then VAT number
     */
    private function extractVatParametersFromCustomer(VatCustomerInterface $customer)
    {
        $vatCountry = $customer->getVatCountry();
        $vatNumber  = $customer->getVatNumber();

        if (null !== $vatCountry) {
            return [$vatCountry, $vatNumber];
        }

        // If vatCountry is not embedded directly into the customer, then we fallback to the credit card country
        if ($card = $customer->getCard()) {
            return [$card->getCountry(), $vatNumber];
        }

        return [$vatCountry, $vatNumber];
    }
}