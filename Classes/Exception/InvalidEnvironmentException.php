<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\Exception;

/*
 * Copyright 2018 Agentur am Wasser | Maeder & Partner AG
 *
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * InvalidEnvironmentException gets thrown when CsrfRegistry starts up
 * and no valid login session exists.
 */
class InvalidEnvironmentException extends \RuntimeException
{
}
