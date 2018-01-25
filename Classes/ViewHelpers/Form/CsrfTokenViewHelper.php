<?php
declare(strict_types=1);

namespace AawTeam\Nocsrf\ViewHelpers\Form;

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

use AawTeam\Nocsrf\Session\CsrfRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * CsrfTokenViewHelper
 */
class CsrfTokenViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Form\AbstractFormFieldViewHelper
{
    const TOKEN_ID_IDENTIFIER = 'NOCSRF_TOKENID';
    const TOKEN_VALUE_IDENTIFIER = 'NOCSRF_TOKEN';

    /**
     * Renders two hidden input fields that can be used to protect the
     * request (triggered by sending the form).
     *
     * Usage:
     *
     *   <html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
     *         xmlns:nocsrf="http://typo3.org/ns/AawTeam/Nocsrf/ViewHelpers">
     *       <f:form>
     *           <nocsrf:form.csrftoken />
     *       </f:form>
     *   </html>
     *
     * @return string
     */
    public function render()
    {
        /** @var CsrfRegistry $csrfRegistry */
        $csrfRegistry = GeneralUtility::makeInstance(CsrfRegistry::class);
        list($identifier, $token) = $csrfRegistry->generateTokenAndIdentifier();

        // Create the identifier tag
        $name = $this->prefixFieldName(self::TOKEN_ID_IDENTIFIER);
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->tag->reset();
        $this->tag->setTagName('input');
        $this->tag->addAttributes([
            'type' => 'hidden',
            'name' => $name,
            'value' => $identifier
        ]);
        $out = $this->tag->render();

        // Create the value tag
        $name = $this->prefixFieldName(self::TOKEN_VALUE_IDENTIFIER);
        $this->registerFieldNameForFormTokenGeneration($name);
        $this->tag->reset();
        $this->tag->setTagName('input');
        $this->tag->addAttributes([
            'type' => 'hidden',
            'name' => $name,
            'value' => $token
        ]);
        return $out . $this->tag->render();
    }
}
