<?php
/**
 * PHP SMS.
 *
 * @author    Jacques Marneweck <jacques@siberia.co.za>
 * @copyright 2020-2021 Jacques Marneweck.  All rights strictly reserved.
 */

declare(strict_types=1);

namespace Jacques\SMS\Clickatell;

/**
 * Clickatell does not enforce certain parameters and features by default and
 * may drop the feature on their least-cost route which does not support the
 * feature.
 *
 * Required features ensures that Clickatell will route the SMS via the gateway
 * which supports the feature.
 */
class Feature
{
    /**
     * Required features.  FEAT_8BIT, FEAT_UDH, FEAT_UCS2 and FEAT_CONCAT are
     * set by default by Clickatell.
     */

    /**
     * Text – set by default.
     *
     * @var int
     */
    const FEAT_TEXT = 1;

    /**
     * 8-bit messaging – set by default.
     *
     * @var int
     */
    const FEAT_8BIT = 2;

    /**
     * UDH (Binary) - set by default.
     *
     * @var int
     */
    const FEAT_UDH = 4;

    /**
     * UCS2 / Unicode – set by default.
     *
     * @var int
     */
    const FEAT_UCS2 = 8;

    /**
     * Alpha source address (from parameter).
     *
     * @var int
     */
    const FEAT_ALPHA = 16;

    /**
     * Numeric source address (from parameter).
     *
     * @var int
     */
    const FEAT_NUMBER = 32;

    /**
     * Flash messaging.
     *
     * @var int
     */
    const FEAT_FLASH = 512;

    /**
     * Delivery acknowledgments.
     *
     * @var int
     */
    const FEAT_DELIVACK = 8_192;

    /**
     * Concatenation – set by default.
     *
     * @var int
     */
    const FEAT_CONCAT = 16_384;
}
