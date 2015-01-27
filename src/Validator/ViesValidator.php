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

namespace ZfrCash\Validator;

use Ddeboer\Vatin\Validator as BaseViesValidator;
use Zend\Validator\AbstractValidator;
use Zend\Validator\Exception;

/**
 * Validator used to validate VIES number
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 */
class ViesValidator extends AbstractValidator
{
    const INVALID_NUMBER = 'invalidNumber';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [
        self::INVALID_NUMBER => 'VIES VAT number is not valid'
    ];

    /**
     * @var BaseViesValidator
     */
    protected $viesValidator;

    /**
     * @var bool
     */
    protected $checkExistence = false;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->viesValidator = new BaseViesValidator();

        // Because ZF2 does not follow recent conventions...
        if (isset($options['check_existence'])) {
            $options['checkExistence'] = $options['check_existence'];
        }

        parent::__construct($options);
    }

    /**
     * If set to true, this will do an additional call to the VIES webservice to check the existence of the VAT number
     *
     * NOTE: from my experience, the VIES webservice is HIGHLY unreliable and often fails
     *
     * @param bool $checkExistence
     */
    public function setCheckExistence($checkExistence)
    {
        $this->checkExistence = (bool) $checkExistence;
    }

    /**
     * Does the validator check existence against VIES webservice?
     *
     * @return bool
     */
    public function getCheckExistence()
    {
        return $this->checkExistence;
    }

    /**
     * {@inheritDoc}
     */
    public function isValid($value)
    {
        $this->setValue($value);

        if (!$this->viesValidator->isValid($value, $this->checkExistence)) {
            $this->error(self::INVALID_NUMBER);
            return false;
        }

        return true;
    }
}
