<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/
 
namespace ILIAS\BackgroundTasks\Implementation\Values\ScalarValues;

use ILIAS\BackgroundTasks\Exceptions\InvalidArgumentException;
use ILIAS\BackgroundTasks\Value;

class StringValue extends ScalarValue
{
    public function setValue($value) : void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException("The value given must be a string! See php-documentation is_string().");
        }
        parent::setValue($value);
    }
}
