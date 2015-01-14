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

namespace ZfrCash\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * @var string
     */
    protected $objectManager = 'doctrine.entitymanager.orm_default';

    /**
     * @var bool
     */
    protected $validateWebhooks = true;

    /**
     * @var bool
     */
    protected $registerListeners = true;

    /**
     * @var string
     */
    protected $vatNumber;

    /**
     * @var string
     */
    protected $vatCountry;

    /**
     * @param string $objectManager
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = (string) $objectManager;
    }

    /**
     * @return string
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param bool $validateWebhooks
     */
    public function setValidateWebhooks($validateWebhooks)
    {
        $this->validateWebhooks = (bool) $validateWebhooks;
    }

    /**
     * @return bool
     */
    public function getValidateWebhooks()
    {
        return $this->validateWebhooks;
    }

    /**
     * @param bool $registerListeners
     */
    public function setRegisterListeners($registerListeners)
    {
        $this->registerListeners = (bool) $registerListeners;
    }

    /**
     * @return bool
     */
    public function getRegisterListeners()
    {
        return $this->registerListeners;
    }

    /**
     * @param string $vatNumber
     */
    public function setVatNumber($vatNumber)
    {
        $this->vatNumber = (string) $vatNumber;
    }

    /**
     * @return string
     */
    public function getVatNumber()
    {
        return $this->vatNumber;
    }

    /**
     * @param string $vatCountry
     */
    public function setVatCountry($vatCountry)
    {
        $this->vatCountry = (string) $vatCountry;
    }

    /**
     * @return string
     */
    public function getVatCountry()
    {
        return $this->vatCountry;
    }
}
