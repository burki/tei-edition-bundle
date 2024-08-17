<?php

namespace TeiEditionBundle\Utils;

/**
 * Copy from abandoned package
 *  https://github.com/Brunty/php-relax-ng-domdocument
 * licensed as follows:
 *
 * MIT License
 * Copyright (c) 2017 Matt Brunt matt@mfyu.co.uk
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to
 * whom the Software is furnished to do so, subject to the
 * following conditions:
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class DOMDocument extends \DOMDocument
{

    /**
     * @var array
     */
    private $validationWarnings = [];

    /**
     * {@inheritdoc}
     */
    public function relaxNGValidate($filename): bool
    {
        $this->setErrorHandler();
        $result = parent::relaxNGValidate($filename);
        restore_error_handler();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function relaxNGValidateSource($string): bool
    {
        $this->setErrorHandler();
        $result = parent::relaxNGValidateSource($string);
        restore_error_handler();

        return $result;
    }

    /**
     * @return array
     */
    public function getValidationWarnings()
    {
        return $this->validationWarnings;
    }

    private function setErrorHandler()
    {
        $this->validationWarnings = [];
        set_error_handler(
            function ($errNumber, $errString) {
                $this->validationWarnings[] = $errString;
            }
        );
    }
}
